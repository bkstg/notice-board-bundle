<?php

namespace Bkstg\NoticeBoardBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Route;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\User;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\NoticeBoardBundle\Form\PostType;
use Bkstg\CoreBundle\Manager\MessageManager;

/**
 * @Route\Route("/board")
 */
class BoardController extends Controller
{
    /**
     * @Route\Route("/", name="bkstg_board_home")
     */
    public function indexAction(Http\Request $request)
    {
        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // get root level posts
        $dql = "SELECT p FROM BkstgNoticeBoardBundle:Post p WHERE p.parent IS NULL ORDER BY p.created DESC";
        $query = $em->createQuery($dql);

        // paginate
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $request->query->getInt('page', 1), 10);

        // create new post form
        $post_type = new PostType();
        $new_post = new Post();

        // new form
        $new_form = $this->createForm($post_type, $new_post, array(
            'action' => $this->generateUrl('bkstg_board_add_post'),
        ))->createView();

        // create reply forms
        foreach ($posts as $post) {
            $post_type->setName('bkstg_boardbundle_post_' . $post->getId());
            $post->form = $this->createForm($post_type, $new_post, array(
                'action' => $this->generateUrl('bkstg_board_add_post', array(
                    'parent' => $post,
                )),
            ))->createView();
        }

        // get message manager
        $message_manager = $this->get('message.manager');

        // render the notice board
        return $this->render('BkstgNoticeBoardBundle:Board:board.html.twig', array(
            'new_form' => $new_form,
            'posts' => $posts,
            'message_manager' => $message_manager,
        ));
    }

    /**
     * @Route\Route("/add/{parent}", defaults={"parent"=0}, name="bkstg_board_add_post")
     * @Route\Method("POST")
     */
    public function addAction($parent, Http\Request $request)
    {
        // get current user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get repo and parent
        $repository = $this->getDoctrine()
            ->getRepository('BkstgNoticeBoardBundle:Post');
        $parent = $repository->findOneById($parent);

        // post type generation
        $post = new Post();
        $post_type = new PostType();
        if ($parent !== null) {
            $post_type->setName('bkstg_boardbundle_post_' . $parent->getId());
            $post->setParent($parent);
        }

        // create new post form
        $form = $this->createForm($post_type, $post);

        // if form is valid flush doctrine
        $form->handleRequest($request);
        $post->setUser($user);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            // success message
            $this->addFlash(
                'success',
                'Notice board post added!'
            );

            $message_manager = $this->get('message.manager');

            if ($parent !== null) {
                $message = "$user replied to a post on the notice board";
            } else {
                $message = "$user posted to the notice board";
            }
            $message_manager->createMessage($message, 'th-list', 'bkstg_board_home', null, 'BkstgNoticeBoardBundle:Post', $post);
        }

        // redirect back to the board
        return $this->redirectToRoute('bkstg_board_home');
    }

    /**
     * @Route\Route("/edit/{post}", name="bkstg_board_edit_post")
     * @Route\ParamConverter("post", class="BkstgNoticeBoardBundle:Post")
     */
    public function editAction(Post $post, Http\Request $request)
    {
        // check this user has access
        $this->denyAccessUnlessGranted('edit', $post, 'Unauthorized access!');

        // user
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // get entity manager and generate form handler
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new PostType(), $post);

        // handle this form and redirect
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($post);
            $em->flush();

            // success message
            $this->addFlash(
                'success',
                'Notice board post updated.'
            );

            $message_manager = $this->get('message.manager');
            $message_manager->createMessage($user->__toString() . ' edited a post on the notice board', 'th-list', 'bkstg_board_home', null, 'BkstgNoticeBoardBundle:Post', $post);

            // redirect back to the board
            return $this->redirectToRoute('bkstg_board_home');
        }

        // get message manager
        $message_manager = $this->get('message.manager');

        return $this->render('BkstgCoreBundle:Generic:form.html.twig', array(
            'form' => $form->createView(),
            'title' => 'Edit Post',
            'submit_value' => 'Save changes',
            'message_manager' => $message_manager,
        ));
    }

    /**
     * @Route\Route("/delete/{post}", name="bkstg_board_delete_post")
     * @Route\ParamConverter("post", class="BkstgNoticeBoardBundle:Post")
     */
    public function deleteAction(Post $post, Http\Request $request)
    {
        // check this user has access
        $this->denyAccessUnlessGranted('edit', $post, 'Unauthorized access!');

        // get entity manager
        $em = $this->getDoctrine()->getManager();

        // remove all child posts
        foreach($post->getChildren() as $child) {
            $em->remove($child);
        }

        $em->remove($post);
        $em->flush();

        // success message
        $this->addFlash(
            'warning',
            'Notice board post was deleted.'
        );

        // redirect to board
        return $this->redirectToRoute('bkstg_board_home');
    }
}

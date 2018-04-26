<?php

namespace Bkstg\NoticeBoardBundle\Controller;

use Bkstg\CoreBundle\Context\ContextProviderInterface;
use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\NoticeBoardBundle\Form\PostType;
use Bkstg\NoticeBoardBundle\Form\ReplyType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PostController extends Controller
{
    public function createAction(
        $production_slug,
        Request $request,
        TokenStorageInterface $token,
        AuthorizationCheckerInterface $auth
    ) {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_USER', $production)) {
            throw new AccessDeniedException();
        }

        // Get some basic information about the user.
        $user = $token->getToken()->getUser();

        // Create a new post.
        $post = new Post();
        $post->setStatus(Post::STATUS_ACTIVE);
        $post->setPinned(false);
        $post->setAuthor($user->getUsername());
        $post->addGroup($production);

        // This post is a reply.
        if ($request->query->has('reply-to')) {
            // Make sure the parent post is valid.
            $repo = $this->em->getRepository(Post::class);
            if (null === $parent = $repo->findOneBy(['id' => $request->query->get('reply-to')])) {
                throw new NotFoundHttpException();
            }

            // Must be a member of the same group.
            if (!$parent->getGroups()->contains($production)) {
                throw new AccessDeniedException();
            }

            // Parent must not be a child.
            if ($parent->getParent() !== null) {
                throw new AccessDeniedException();
            }
            $post->setParent($parent);

            // This is a reply, use the basic reply form.
            $form = $this->form->create(ReplyType::class, $post);
        } else {
            // This is a new post, use the post form.
            $form = $this->form->create(PostType::class, $post);
        }

        // Handle the form.
        $form->handleRequest($request);

        // Form is submitted and valid.
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the post.
            $this->em->persist($post);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('post.created', [], 'BkstgNoticeBoardBundle')
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_board_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgNoticeBoard/Post/create.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'production' => $production,
        ]));
    }

    public function updateAction(
        $production_slug,
        $id,
        Request $request,
        TokenStorageInterface $token,
        AuthorizationCheckerInterface $auth
    ) {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Lookup the post by id.
        $post_repo = $this->em->getRepository(Post::class);
        if (null === $post = $post_repo->findOneBy(['id' => $id])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $post)) {
            throw new AccessDeniedException();
        }

        // Get some basic information about the user.
        $user = $token->getToken()->getUser();

        // Create a new form for the post and handle.
        if ($post->getParent() !== null) {
            $form = $this->form->create(ReplyType::class, $post);
        } else {
            $form = $this->form->create(PostType::class, $post);
        }

        // Handle the form submission.
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Flush entity manager, set success message and redirect.
            $this->em->flush();
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('post.updated', [], 'BkstgNoticeBoardBundle')
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_board_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgNoticeBoard/Post/update.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'production' => $production,
        ]));
    }
}

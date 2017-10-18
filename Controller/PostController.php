<?php

namespace Bkstg\NoticeBoardBundle\Controller;

use Bkstg\CoreBundle\Context\ContextProviderInterface;
use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\NoticeBoardBundle\Form\PostType;
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

        // Create a new production.
        $post = new Post();
        $post->setStatus(Post::STATUS_ACTIVE);
        $post->setAuthor($user->getUsername());
        $post->addGroup($production);

        // Create and handle the form.
        $form = $this->form->create(PostType::class, $post);
        $form->handleRequest($request);

        // Form is submitted and valid.
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the post.
            $this->em->persist($post);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('Post created.')
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_board_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgCore/Post/create.html.twig', [
            'form' => $form->createView(),
        ]));
    }
}

<?php

namespace Bkstg\NoticeBoardBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\NoticeBoardBundle\BkstgNoticeBoardBundle;
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
    /**
     * Create a new post.
     *
     * @param  string                        $production_slug The production slug.
     * @param  Request                       $request         The incoming request.
     * @param  TokenStorageInterface         $token           The token storage service.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @throws NotFoundHttpException When the production is not found.
     * @throws AccessDeniedException When the user is not a member of the production.
     * @return Response
     */
    public function createAction(
        string $production_slug,
        Request $request,
        TokenStorageInterface $token,
        AuthorizationCheckerInterface $auth
    ): Response {
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
        $post->setActive(true);
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
                $this->translator->trans('post.created', [], BkstgNoticeBoardBundle::TRANSLATION_DOMAIN)
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

    /**
     * Update a post.
     *
     * @param  string                        $production_slug The production slug.
     * @param  integer                       $id              The id of the post.
     * @param  Request                       $request         The incoming request.
     * @param  TokenStorageInterface         $token           The token storage service.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @throws AccessDeniedException When the user is not a member of the production.
     * @return Response
     */
    public function updateAction(
        string $production_slug,
        int $id,
        Request $request,
        TokenStorageInterface $token,
        AuthorizationCheckerInterface $auth
    ): Response {
        // Lookup the post and production.
        list($post, $production) = $this->lookupEntity(Post::class, $id, $production_slug);

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
                $this->translator->trans('post.updated', [], BkstgNoticeBoardBundle::TRANSLATION_DOMAIN)
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

    /**
     * Delete a post.
     *
     * @param  string                        $production_slug The production slug.
     * @param  integer                       $id              The id of the post.
     * @param  Request                       $request         The incoming request.
     * @param  TokenStorageInterface         $token           The token storage service.
     * @param  AuthorizationCheckerInterface $auth            The authorization checker service.
     * @throws AccessDeniedException When the user is not a member of the production.
     * @return Response
     */
    public function deleteAction(
        string $production_slug,
        int $id,
        Request $request,
        TokenStorageInterface $token,
        AuthorizationCheckerInterface $auth
    ): Response {
        // Lookup the post and production.
        list($post, $production) = $this->lookupEntity(Post::class, $id, $production_slug);

        // Check permissions for this action.
        if (!$auth->isGranted('edit', $post)) {
            throw new AccessDeniedException();
        }

        // Create an empty form to submit.
        $form = $this->form->createBuilder()->getForm();
        $form->handleRequest($request);

        // Delete the post.
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($post);
            $this->em->flush();

            // Set success message and redirect.
            $this->session->getFlashBag()->add(
                'success',
                $this->translator->trans('post.deleted', [
                    '%post%' => $post->getName(),
                ], BkstgNoticeBoardBundle::TRANSLATION_DOMAIN)
            );
            return new RedirectResponse($this->url_generator->generate('bkstg_board_show', ['production_slug' => $production->getSlug()]));
        }

        // Render the form.
        return new Response($this->templating->render('@BkstgNoticeBoard/Post/delete.html.twig', [
            'production' => $production,
            'post' => $post,
            'form' => $form->createView(),
        ]));
    }
}

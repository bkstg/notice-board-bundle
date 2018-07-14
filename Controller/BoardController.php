<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Controller;

use Bkstg\CoreBundle\Controller\Controller;
use Bkstg\CoreBundle\Entity\Production;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BoardController extends Controller
{
    /**
     * Show the notice board.
     *
     * @param string                        $production_slug The production slug.
     * @param AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param PaginatorInterface            $paginator       The paginator service.
     * @param Request                       $request         The incoming request.
     *
     * @throws NotFoundHttpException When the production is not found.
     * @throws AccessDeniedException When the user is not a member of the production.
     *
     * @return Response
     */
    public function showAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
        Request $request
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

        // Get notice board posts.
        $query = $this->em->getRepository(Post::class)->getAllActiveQuery($production);

        // Return response.
        $posts = $paginator->paginate($query, $request->query->getInt('page', 1));

        return new Response($this->templating->render('@BkstgNoticeBoard/Board/show.html.twig', [
            'production' => $production,
            'posts' => $posts,
        ]));
    }

    /**
     * Show archive for the notice board.
     *
     * @param string                        $production_slug The production slug.
     * @param AuthorizationCheckerInterface $auth            The authorization checker service.
     * @param PaginatorInterface            $paginator       The paginator service.
     * @param Request                       $request         The incoming request.
     *
     * @throws NotFoundHttpException When the production is not found.
     * @throws AccessDeniedException When the user is not an editor of the production.
     *
     * @return Response
     */
    public function archiveAction(
        string $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // Lookup the production by production_slug.
        $production_repo = $this->em->getRepository(Production::class);
        if (null === $production = $production_repo->findOneBy(['slug' => $production_slug])) {
            throw new NotFoundHttpException();
        }

        // Check permissions for this action.
        if (!$auth->isGranted('GROUP_ROLE_EDITOR', $production)) {
            throw new AccessDeniedException();
        }

        // Get notice board posts.
        $query = $this->em->getRepository(Post::class)->getAllInactiveQuery($production);

        // Return response.
        $posts = $paginator->paginate($query, $request->query->getInt('page', 1));

        return new Response($this->templating->render('@BkstgNoticeBoard/Board/archive.html.twig', [
            'production' => $production,
            'posts' => $posts,
        ]));
    }
}

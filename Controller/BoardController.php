<?php

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
    public function showAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
        Request $request
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

        // Get notice board posts.
        $query = $this->em->getRepository(Post::class)->getAllActiveQuery($production);

        // Return response.
        $posts = $paginator->paginate($query, $request->query->getInt('page', 1));
        return new Response($this->templating->render('@BkstgNoticeBoard/Board/show.html.twig', [
            'production' => $production,
            'posts' => $posts,
        ]));
    }

    public function archiveAction(
        $production_slug,
        AuthorizationCheckerInterface $auth,
        PaginatorInterface $paginator,
        Request $request
    ) {
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

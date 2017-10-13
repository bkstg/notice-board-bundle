<?php

namespace Bkstg\NoticeBoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BoardController extends Controller
{
    public function showAction($production_slug)
    {
        return $this->render('@BkstgNoticeBoard/Board/show.html.twig');
    }
}

<?php

namespace Bkstg\NoticeBoardBundle\Spread;

use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\TimelineBundle\Spread\AdminSpread;
use Spy\Timeline\Model\ActionInterface;

class PostAdminSpread extends AdminSpread
{
    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        $object = $action->getComponent('directComplement')->getData();

        if (!$object instanceof Post) {
            return false;
        }

        return true;
    }
}

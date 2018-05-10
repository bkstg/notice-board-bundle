<?php

namespace Bkstg\NoticeBoardBundle\Spread;

use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\TimelineBundle\Spread\GroupableSpread;
use Spy\Timeline\Model\ActionInterface;

class PostGroupableSpread extends GroupableSpread
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

<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Timeline\Spread;

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
        // Only supports new posts.
        $object = $action->getComponent('directComplement')->getData();
        if (!$object instanceof Post || 'post' != $action->getVerb()) {
            return false;
        }

        return true;
    }
}

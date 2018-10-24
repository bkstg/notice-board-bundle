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
use Bkstg\TimelineBundle\Spread\AdminSpread;
use Spy\Timeline\Model\ActionInterface;

class PostAdminSpread extends AdminSpread
{
    /**
     * {@inheritdoc}
     *
     * @param ActionInterface $action The action to spread.
     *
     * @return bool
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

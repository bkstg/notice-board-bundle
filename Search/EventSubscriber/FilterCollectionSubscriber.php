<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Search\EventSubscriber;

use Bkstg\SearchBundle\Event\FilterCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterCollectionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FilterCollectionEvent::NAME => [
                ['addPostFilter', 0],
            ],
        ];
    }

    public function addPostFilter(FilterCollectionEvent $event): void
    {
        $now = new \DateTime();
        $qb = $event->getQueryBuilder();
        $query = $qb->query()->bool()
            ->addMust($qb->query()->term(['_index' => 'post']))
            ->addMust($qb->query()->term(['active' => true]))
            ->addMust($qb->query()->terms('groups.id', $event->getGroupIds()))
            ->addMust($qb->query()->bool()
                ->addShould($qb->query()->range('expiry', ['gt' => $now->format('U') * 1000]))
                ->addShould($qb->query()->bool()->addMustNot($qb->query()->exists('expiry')))
            )
        ;
        $event->addFilter($query);
    }
}

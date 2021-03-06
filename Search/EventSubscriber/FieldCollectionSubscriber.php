<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Search\EventSubscriber;

use Bkstg\SearchBundle\Event\FieldCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FieldCollectionSubscriber implements EventSubscriberInterface
{
    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents()
    {
        return [
            FieldCollectionEvent::NAME => [
                ['addPostFields', 0],
            ],
        ];
    }

    /**
     * Add post fields to search.
     *
     * @param FieldCollectionEvent $event The menu collection event.
     *
     * @return void
     */
    public function addPostFields(FieldCollectionEvent $event): void
    {
        $event->addFields([
            'body',
            'author',
        ]);
    }
}

<?php

namespace Bkstg\NoticeBoardBundle\Search\EventSubscriber;

use Bkstg\SearchBundle\Event\FieldCollectionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FieldCollectionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FieldCollectionEvent::NAME => [
                ['addPostFields', 0],
            ]
        ];
    }

    public function addPostFields(FieldCollectionEvent $event)
    {
        $event->addFields([
            'body',
            'author',
        ]);
    }
}

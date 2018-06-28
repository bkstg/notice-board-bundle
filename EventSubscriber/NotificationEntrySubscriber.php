<?php

namespace Bkstg\NoticeBoardBundle\EventSubscriber;

use Bkstg\TimelineBundle\Event\NotificationEntryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationEntrySubscriber implements EventSubscriberInterface
{
    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents(): array
    {
        // Use raw notification key, we can't guarantee the bundle is available.
        return [
            'bkstg.timeline.notification_entry' => [
                ['checkPostEntry', 0]
            ]
        ];
    }

    /**
     * Check the entry for whether or not to notify.
     *
     * @param  NotificationEntryEvent $event The notification event.
     * @return void
     */
    public function checkPostEntry(NotificationEntryEvent $event)
    {
        // Get action and entry.
        $action = $event->getAction();
        $entry = $event->getEntry();

        // If this is not a post verb then skip it.
        if ($action->getVerb() != 'post') {
            return;
        }

        // Get the subject of the action and see if it is the same as entry.
        $action_subject = $action->getSubject();
        $entry_subject = $entry->getSubject();

        // If they are the same do not notify.
        if ($action_subject === $entry_subject) {
            $event->setNotify(false);
        }
    }
}

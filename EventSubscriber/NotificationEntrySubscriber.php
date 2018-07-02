<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
                ['checkPostEntry', 0],
            ],
        ];
    }

    /**
     * Check the entry for whether or not to notify.
     *
     * @param NotificationEntryEvent $event The notification event.
     */
    public function checkPostEntry(NotificationEntryEvent $event): void
    {
        // Get action and entry.
        $action = $event->getAction();
        $entry = $event->getEntry();

        // If this is not a post verb then skip it.
        if ('post' != $action->getVerb()) {
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

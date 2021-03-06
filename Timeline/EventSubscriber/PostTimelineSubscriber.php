<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Timeline\EventSubscriber;

use Bkstg\CoreBundle\Event\EntityPublishedEvent;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class PostTimelineSubscriber implements EventSubscriberInterface
{
    private $action_manager;
    private $user_provider;

    /**
     * Create a new post notification listener.
     *
     * @param ActionManagerInterface $action_manager The action manager service.
     * @param UserProviderInterface  $user_provider  The user provider service.
     */
    public function __construct(
        ActionManagerInterface $action_manager,
        UserProviderInterface $user_provider
    ) {
        $this->action_manager = $action_manager;
        $this->user_provider = $user_provider;
    }

    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EntityPublishedEvent::NAME => [
                ['createPostTimelineEntry', 0],
            ],
        ];
    }

    /**
     * Create the timeline entry for the new post.
     *
     * @param EntityPublishedEvent $event The entity published event.
     *
     * @return void
     */
    public function createPostTimelineEntry(EntityPublishedEvent $event): void
    {
        // Only act on post objects.
        $post = $event->getObject();
        if (!$post instanceof Post) {
            return;
        }

        // Get the author for the post.
        $author = $this->user_provider->loadUserByUsername($post->getAuthor());

        // Create components for this action.
        $post_component = $this->action_manager->findOrCreateComponent($post);
        $author_component = $this->action_manager->findOrCreateComponent($author);

        // Add timeline entries for each group.
        foreach ($post->getGroups() as $group) {
            // Create the group component.
            $group_component = $this->action_manager->findOrCreateComponent($group);

            // Either a new post was created or a reply was created.
            if (null === $post->getParent()) {
                $verb = 'post';
            } else {
                $verb = 'reply';
            }

            // Create the action and link it.
            $action = $this->action_manager->create($author_component, $verb, [
                'directComplement' => $post_component,
                'indirectComplement' => $group_component,
            ]);

            // Update the action.
            $this->action_manager->updateAction($action);
        }
    }
}

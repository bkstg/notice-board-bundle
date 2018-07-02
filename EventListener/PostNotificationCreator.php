<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\EventListener;

use Bkstg\NoticeBoardBundle\Entity\Post;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class PostNotificationCreator
{
    private $action_manager;
    private $user_provider;
    private $url_genertor;

    /**
     * Create a new post notification listener.
     *
     * @param ActionManagerInterface $action_manager The action manager service.
     * @param UserProviderInterface  $user_provider  The user provider service.
     * @param UrlGeneratorInterface  $url_generator  The url generator service.
     */
    public function __construct(
        ActionManagerInterface $action_manager,
        UserProviderInterface $user_provider,
        UrlGeneratorInterface $url_generator
    ) {
        $this->action_manager = $action_manager;
        $this->user_provider = $user_provider;
        $this->url_generator = $url_generator;
    }

    /**
     * Listen to post-persist events.
     *
     * @param LifecycleEventArgs $args The lifecycle arguments.
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        // Get the author for the post.
        $author = $this->user_provider->loadUserByUsername($post->getAuthor());

        // Create components for this action.
        $post_component = $this->action_manager->findOrCreateComponent($post);
        $author_component = $this->action_manager->findOrCreateComponent($author);

        foreach ($post->getGroups() as $group) {
            $group_component = $this->action_manager->findOrCreateComponent($group);

            $action = $this->action_manager->create(
                $author_component,
                'post',
                [
                    'directComplement' => $post_component,
                    'indirectComplement' => $group_component,
                ]
            );
            $action->setLink($this->url_generator->generate(
                'bkstg_board_show',
                [
                    '_fragment' => 'post-' . $post->getId(),
                    'production_slug' => $group->getSlug(),
                ]
            ));
            $this->action_manager->updateAction($action);
        }
    }
}

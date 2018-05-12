<?php

namespace Bkstg\NoticeBoardBundle\EventListener;

use Bkstg\CoreBundle\User\UserProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Spy\Timeline\Driver\ActionManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostCreator
{
    private $action_manager;
    private $user_provider;
    private $url_genertor;

    public function __construct(
        ActionManagerInterface $action_manager,
        UserProviderInterface $user_provider,
        UrlGeneratorInterface $url_generator
    ) {
        $this->action_manager = $action_manager;
        $this->user_provider = $user_provider;
        $this->url_generator = $url_generator;
    }

    public function postPersist(LifecycleEventArgs $args)
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
                    '_fragment' => 'post-'.$post->getId(),
                    'production_slug' => $group->getSlug()
                ]
            ));
            $this->action_manager->updateAction($action);
        }
    }
}

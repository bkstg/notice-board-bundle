<?php

namespace Bkstg\NoticeBoardBundle\EventListener;

use Bkstg\CoreBundle\User\UserProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Spy\Timeline\Driver\ActionManagerInterface;

class PostCreator
{
    private $action_manager;
    private $user_provider;

    public function __construct(
        ActionManagerInterface $action_manager,
        UserProviderInterface $user_provider
    ) {
        $this->action_manager = $action_manager;
        $this->user_provider = $user_provider;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if (!$post instanceof Post) {
            return;
        }

        $user = $this->user_provider->loadUserByUsername($post->getAuthor());

        $user_component = $this->action_manager->findOrCreateComponent($user);
        foreach ($post->getGroups() as $group) {
            $group_component = $this->action_manager->findOrCreateComponent($group);
            $action = $this->action_manager->create($user_component, 'post', array('directComplement' => $group_component));
            $this->action_manager->updateAction($action);
        }
    }
}

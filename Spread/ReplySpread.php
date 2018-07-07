<?php

namespace Bkstg\NoticeBoardBundle\Spread;

use Bkstg\CoreBundle\User\UserInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Spy\Timeline\Model\ActionInterface;
use Spy\Timeline\Spread\Entry\EntryCollection;
use Spy\Timeline\Spread\Entry\EntryUnaware;
use Spy\Timeline\Spread\SpreadInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ReplySpread implements SpreadInterface
{
    private $user_provider;

    public function __construct(UserProviderInterface $user_provider)
    {
        $this->user_provider = $user_provider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ActionInterface $action)
    {
        // Only supports new posts.
        $object = $action->getComponent('directComplement')->getData();
        if (!$object instanceof Post || $action->getVerb() != 'reply') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ActionInterface $action, EntryCollection $collection)
    {
        $post = $action->getComponent('directComplement')->getData();
        $parent = $post->getParent();

        // Spread to authors of posts in the thread.
        $check = array_merge([$post], $parent->getChildren()->toArray());
        $done = [];
        foreach ($parent->getChildren() as $child) {
            // Load the author, spread if they are not the new reply author and
            // they have not been spread to yet.
            $author = $this->user_provider->loadUserByUsername($child->getAuthor());
            if (!$author instanceof UserInterface
                || $author->getUsername() == $post->getAuthor()
                || isset($done[$author->getUsername()])) {
                continue;
            }

            $done[$author->getUsername()] = true;
            $collection->add(new EntryUnaware(get_class($author), $author->getId()));
        }
    }
}

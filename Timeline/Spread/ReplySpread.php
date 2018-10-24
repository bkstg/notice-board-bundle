<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Timeline\Spread;

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

    /**
     * Create a new reply spread.
     *
     * @param UserProviderInterface $user_provider The user provider service.
     */
    public function __construct(UserProviderInterface $user_provider)
    {
        $this->user_provider = $user_provider;
    }

    /**
     * {@inheritdoc}
     *
     * @param ActionInterface $action The action to spread.
     *
     * @return bool
     */
    public function supports(ActionInterface $action)
    {
        // Only supports new posts.
        $object = $action->getComponent('directComplement')->getData();
        if (!$object instanceof Post || 'reply' != $action->getVerb()) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param ActionInterface $action     The action to spread.
     * @param EntryCollection $collection The current spreads.
     *
     * @return void
     */
    public function process(ActionInterface $action, EntryCollection $collection): void
    {
        $post = $action->getComponent('directComplement')->getData();
        $parent = $post->getParent();

        // Spread to authors of posts in the thread.
        $posts = array_merge([$parent], $parent->getChildren()->toArray());
        $done = [];
        foreach ($posts as $check) {
            // Load the author, spread if they have not been spread to yet.
            $author = $this->user_provider->loadUserByUsername($check->getAuthor());
            if (!$author instanceof UserInterface
                || isset($done[$author->getUsername()])) {
                continue;
            }

            $done[$author->getUsername()] = true;
            $collection->add(new EntryUnaware(get_class($author), $author->getId()));
        }
    }
}

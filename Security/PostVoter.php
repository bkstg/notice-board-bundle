<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\NoticeBoardBundle\Entity\Post;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PostVoter extends GroupableEntityVoter
{
    /**
     * {@inheritdoc}
     *
     * @param mixed $attribute The attribute to vote on.
     * @param mixed $subject   The subject to vote on.
     *
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on Groupable objects inside this voter
        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    /**
     * Allow authors to edit and delete their posts.
     *
     * @param GroupableInterface $post  The post to vote on.
     * @param TokenInterface     $token The token to vote on.
     *
     * @return bool
     */
    public function canEdit(GroupableInterface $post, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if ($post->getAuthor() == $user->getUsername()) {
            return true;
        }

        foreach ($event->getGroups() as $group) {
            if ($this->decision_manager->decide($token, ['GROUP_ROLE_ADMIN'], $group)) {
                return true;
            }
        }

        return false;
    }
}

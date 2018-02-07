<?php

namespace Bkstg\NoticeBoardBundle\Security;

use Bkstg\CoreBundle\Security\GroupableEntityVoter;
use Bkstg\NoticeBoardBundle\Entity\Post;
use MidnightLuke\GroupSecurityBundle\Model\GroupableInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PostVoter extends GroupableEntityVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
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

    public function canEdit(GroupableInterface $post, TokenInterface $token)
    {
        $user = $token->getUser();
        $decision = parent::canEdit($post, $token);
        if ($decision === false) {
            return ($post->getAuthor() == $user->getUsername());
        }
        return $decision;
    }
}

<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Timeline\EventSubscriber;

use Bkstg\TimelineBundle\Event\TimelineLinkEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostLinkSubscriber implements EventSubscriberInterface
{
    private $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    public static function getSubscribedEvents()
    {
        return [
            TimelineLinkEvent::NAME => [
                ['setPostLink', 0],
            ],
        ];
    }

    public function setPostLink(TimelineLinkEvent $event): void
    {
        $action = $event->getAction();

        if (!in_array($action->getVerb(), ['post', 'reply'])) {
            return;
        }

        $production = $action->getComponent('indirectComplement')->getData();
        $post = $action->getComponent('directComplement')->getData();
        $event->setLink($this->url_generator->generate('bkstg_board_show', [
            'production_slug' => $production->getSlug(),
            '_fragment' => 'post-' . $post->getId(),
        ]));
    }
}

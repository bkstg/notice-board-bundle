<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\ProductionMenuCollectionEvent;
use Bkstg\NoticeBoardBundle\BkstgNoticeBoardBundle;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProductionMenuSubscriber implements EventSubscriberInterface
{
    private $factory;
    private $auth;

    /**
     * Create a new production menu subscriber.
     *
     * @param FactoryInterface              $factory The menu factory service.
     * @param AuthorizationCheckerInterface $auth    The authorization checker service.
     */
    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $auth
    ) {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    /**
     * Return the events this subscriber listens for.
     *
     * @return array The subscribed events.
     */
    public static function getSubscribedEvents(): array
    {
        return [
           ProductionMenuCollectionEvent::NAME => [
               ['addNoticeBoardItem', 10],
           ],
        ];
    }

    /**
     * Add the notice board menu item.
     *
     * @param ProductionMenuCollectionEvent $event The menu collection event.
     *
     * @return void
     */
    public function addNoticeBoardItem(ProductionMenuCollectionEvent $event): void
    {
        $menu = $event->getMenu();
        $group = $event->getGroup();

        // Create notice_board menu item.
        $board = $this->factory->createItem('menu_item.notice_board', [
            'route' => 'bkstg_board_show',
            'routeParameters' => ['production_slug' => $group->getSlug()],
            'extras' => [
                'icon' => 'comment-o',
                'translation_domain' => BkstgNoticeBoardBundle::TRANSLATION_DOMAIN,
            ],
        ]);
        $menu->addChild($board);

        // If this user is an editor create the post and archive items.
        if ($this->auth->isGranted('GROUP_ROLE_EDITOR', $group)) {
            $posts = $this->factory->createItem('menu_item.notice_board_posts', [
                'route' => 'bkstg_board_show',
                'routeParameters' => ['production_slug' => $group->getSlug()],
                'extras' => ['translation_domain' => BkstgNoticeBoardBundle::TRANSLATION_DOMAIN],
            ]);
            $board->addChild($posts);

            $archive = $this->factory->createItem('menu_item.notice_board_archive', [
                'route' => 'bkstg_board_archive',
                'routeParameters' => ['production_slug' => $group->getSlug()],
                'extras' => ['translation_domain' => BkstgNoticeBoardBundle::TRANSLATION_DOMAIN],
            ]);
            $board->addChild($archive);
        }
    }
}

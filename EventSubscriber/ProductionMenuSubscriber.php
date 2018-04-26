<?php

namespace Bkstg\NoticeBoardBundle\EventSubscriber;

use Bkstg\CoreBundle\Event\ProductionMenuCollectionEvent;
use Bkstg\CoreBundle\Menu\Item\IconMenuItem;
use Knp\Menu\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProductionMenuSubscriber implements EventSubscriberInterface
{

    private $factory;
    private $url_generator;
    private $auth;
    private $translator;

    public function __construct(
        FactoryInterface $factory,
        UrlGeneratorInterface $url_generator,
        AuthorizationCheckerInterface $auth,
        TranslatorInterface $translator
    ) {
        $this->factory = $factory;
        $this->url_generator = $url_generator;
        $this->auth = $auth;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
           ProductionMenuCollectionEvent::NAME => array(
               array('addNoticeBoardItem', 10),
           )
        );
    }

    public function addNoticeBoardItem(ProductionMenuCollectionEvent $event)
    {
        $menu = $event->getMenu();
        $group = $event->getGroup();

        // Create overview menu item.
        $overview = $this->factory->createItem(
            $this->translator->trans('menu.notice_board', [], 'BkstgNoticeBoardBundle'), [
            'uri' => $this->url_generator->generate(
                'bkstg_board_show',
                ['production_slug' => $group->getSlug()]
            ),
            'extras' => ['icon' => 'comment-o'],
        ]);
        $menu->addChild($overview);
    }
}

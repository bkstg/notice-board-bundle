<?php

namespace Bkstg\NoticeBoardBundle;

use Bkstg\CoreBundle\Event\MenuCollectionEvent;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BkstgNoticeBoardBundle extends Bundle
{
    public function build(ContainerBuilder $container) {
        // $dispatcher = $container->get('event_dispatcher');
        // $dispatcher->addListener(MenuCollectionEvent::NAME, function (MenuCollectionEvent $event) {
        //     d($event);
        // });
    }
}

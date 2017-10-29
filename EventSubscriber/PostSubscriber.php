<?php

namespace Bkstg\NoticeBoardBundle\EventSubscriber;

use Bkstg\NoticeBoardBundle\Entity\Post;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Sonata\NotificationBundle\Backend\BackendInterface;

class PostSubscriber implements EventSubscriber
{
    private $notifier;

    public function __construct(BackendInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if ($post instanceof Post) {
            $this->notifier->createAndPublish('mailer', array(
                'from' => array(
                    'email' => 'no-reply@sonata-project.org',
                    'name'  => 'No Reply'
                ),
                'to'   => array(
                    'myuser@example.org' => 'My User',
                    'myuser1@example.org' => 'My User 1',
                ),
                'message' => array(
                    'html' => '<b>hello</b>',
                    'text' => 'hello'
                ),
                'subject' => 'Contact form',
            ));
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $post = $args->getObject();

        if ($post instanceof Post) {
            $this->notifier->createAndPublish('mailer', array(
                'from' => array(
                    'email' => 'no-reply@sonata-project.org',
                    'name'  => 'No Reply'
                ),
                'to'   => array(
                    'myuser@example.org' => 'My User',
                    'myuser1@example.org' => 'My User 1',
                ),
                'message' => array(
                    'html' => '<b>hello</b>',
                    'text' => 'hello'
                ),
                'subject' => 'Contact form',
            ));
        }
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postRemove',
        ];
    }
}

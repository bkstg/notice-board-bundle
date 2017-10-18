<?php

namespace Bkstg\NoticeBoardBundle\Block;

use Bkstg\CoreBundle\Context\ContextProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\NoticeBoardBundle\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

class PostFormBlock extends AbstractAdminBlockService
{
    protected $em;
    protected $token_storage;
    protected $form;
    protected $context;

    public function __construct(
        $name,
        EngineInterface $templating,
        EntityManagerInterface $em,
        TokenStorageInterface $token_storage,
        FormFactoryInterface $form,
        ContextProviderInterface $context
    ) {
        $this->token_storage = $token_storage;
        $this->em = $em;
        $this->form = $form;
        $this->context = $context;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $group = $this->context->getContext();

        // If not post is passed in create one.
        if (null === $post = $blockContext->getSetting('post')) {
            $post = new Post();
            $post->setStatus(Post::STATUS_ACTIVE);
            $post->setAuthor($this->token_storage->getToken()->getUser()->getUsername());
            $post->addGroup($group);
            $blockContext->setSetting('post', $post);
        }

        // We don't need status or expiry on the block form.
        $form = $this->form
            ->create(PostType::class, new Post());

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'production' => $group,
            'form' => $form->createView(),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'post' => null,
            'template' => '@BkstgNoticeBoard/Block/post-form.html.twig',
        ));
    }
}

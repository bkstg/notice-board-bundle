<?php

namespace Bkstg\NoticeBoardBundle\Block;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Bkstg\NoticeBoardBundle\Form\ReplyType;
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

class ReplyFormBlock extends AbstractAdminBlockService
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
        ProductionContextProviderInterface $context
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

        // If no post is passed in create one.
        if (null === $parent = $blockContext->getSetting('parent')) {
            return null;
        }

        $post = new Post();
        $post->setStatus(Post::STATUS_ACTIVE);
        $post->setAuthor($this->token_storage->getToken()->getUser()->getUsername());
        $post->addGroup($group);
        $post->setParent($parent);
        $blockContext->setSetting('post', $post);

        // Create a post form.
        $form = $this->form->create(ReplyType::class, $post);

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
            'parent' => null,
            'post' => null,
            'template' => '@BkstgNoticeBoard/Block/reply-form.html.twig',
        ));
    }
}

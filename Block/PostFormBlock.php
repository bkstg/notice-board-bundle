<?php

namespace Bkstg\NoticeBoardBundle\Block;

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

    public function __construct(
        $name,
        EngineInterface $templating,
        EntityManagerInterface $em,
        TokenStorageInterface $token_storage,
        FormFactoryInterface $form
    ) {
        $this->token_storage = $token_storage;
        $this->em = $em;
        $this->form = $form;
        parent::__construct($name, $templating);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $form = $this->form->create(PostType::class, new Post());

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'form' => $form->createView(),
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'content' => 'Insert your custom content here',
            'template' => '@BkstgNoticeBoard/Block/post-form.html.twig',
        ));
    }
}

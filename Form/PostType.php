<?php

namespace Bkstg\NoticeBoardBundle\Form;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PostType extends AbstractType
{
    private $context;
    private $auth;

    public function __construct(
        ProductionContextProviderInterface $context,
        AuthorizationCheckerInterface $auth
    ) {
        $this->context = $context;
        $this->auth = $auth;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', CKEditorType::class, [
                'required' => false,
                'config' => ['toolbar' => 'basic'],
                'label' => 'post.form.body',
            ])
        ;

        if ($this->auth->isGranted('GROUP_ROLE_EDITOR', $this->context->getContext())) {
            $builder
                ->add('pinned', CheckboxType::class, [
                    'required' => false,
                    'label' => 'post.form.pinned',
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Active' => Post::STATUS_ACTIVE,
                        'Closed' => Post::STATUS_CLOSED,
                    ],
                    'label' => 'post.form.status',
                ])
                ->add('expiry', DateTimeType::class, [
                    'required' => false,
                    'time_widget' => 'single_text',
                    'date_widget' => 'single_text',
                    'label' => 'post.form.expiry',
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'BkstgNoticeBoardBundle',
            'data_class' => Post::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'bkstg_noticeboardbundle_post';
    }
}

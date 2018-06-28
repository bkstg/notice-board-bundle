<?php

namespace Bkstg\NoticeBoardBundle\Form;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
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

    /**
     * Create a new post form.
     *
     * @param ProductionContextProviderInterface $context The production context service.
     * @param AuthorizationCheckerInterface      $auth    The authorization checker service.
     */
    public function __construct(
        ProductionContextProviderInterface $context,
        AuthorizationCheckerInterface $auth
    ) {
        $this->context = $context;
        $this->auth = $auth;
    }

    /**
     * {@inheritdoc}
     *
     * @param  FormBuilderInterface $builder The form builder.
     * @param  array                $options The form options.
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('body', CKEditorType::class, [
                'required' => false,
                'config' => ['toolbar' => 'basic'],
                'label' => 'post.form.body',
            ])
        ;

        // Add admin things if the user is an admin.
        if ($this->auth->isGranted('GROUP_ROLE_EDITOR', $this->context->getContext())) {
            $builder
                ->add('pinned', CheckboxType::class, [
                    'required' => false,
                    'label' => 'post.form.pinned',
                ])
                ->add('active', ChoiceType::class, [
                    'choices' => [
                        'Active' => true,
                        'Closed' => false,
                    ],
                    'label' => 'post.form.active',
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
     *
     * @param  OptionsResolver $resolver The option resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'BkstgNoticeBoardBundle',
            'data_class' => Post::class,
        ]);
    }
}

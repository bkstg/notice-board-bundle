<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgCoreBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Form;

use Bkstg\CoreBundle\Context\ProductionContextProviderInterface;
use Bkstg\NoticeBoardBundle\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
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
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The form options.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $post = $options['data'];
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
                    // Show "unpublished" instead of active.
                    'choice_loader' => new CallbackChoiceLoader(function () use ($post) {
                        yield 'post.form.status_choices.active' => true;
                        if (!$post->isPublished()) {
                            yield 'post.form.status_choices.unpublished' => false;
                        } else {
                            yield 'post.form.status_choices.archived' => false;
                        }
                    }),
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
     *
     * @param OptionsResolver $resolver The option resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'BkstgNoticeBoardBundle',
            'data_class' => Post::class,
        ]);
    }
}

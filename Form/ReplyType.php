<?php

namespace Bkstg\NoticeBoardBundle\Form;

use Bkstg\NoticeBoardBundle\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplyType extends AbstractType
{
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
    }

    /**
     * {@inheritdoc}
     *
     * @param  OptionsResolver $resolver The option resolver.
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'BkstgNoticeBoardBundle',
            'data_class' => Post::class,
        ]);
    }
}

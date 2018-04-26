<?php

namespace Bkstg\NoticeBoardBundle\Form;

use Bkstg\NoticeBoardBundle\Entity\Post;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplyType extends AbstractType
{
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
        return 'bkstg_noticeboardbundle_reply';
    }
}

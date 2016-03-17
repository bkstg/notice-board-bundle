<?php

namespace Bkstg\NoticeBoardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Bkstg\NoticeBoardBundle\Form\DataTransformer\PostToNumberTransformer;
use Bkstg\CoreBundle\Form\DataTransformer\UserToNumberTransformer;

class PostType extends AbstractType
{

    private $name = 'bkstg_boardbundle_post';

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('body', 'textarea', array('label' => 'Write a post...'));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'Bkstg\NoticeBoardBundle\Entity\Post'
            ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}

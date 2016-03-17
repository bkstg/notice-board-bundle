<?php

namespace Bkstg\NoticeBoardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Bkstg\NoticeBoardBundle\Entity\Post;

class PostToNumberTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($post)
    {
        if (null === $post) {
            return "";
        }

        return $post->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $id
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $post = $this->om
            ->getRepository('BkstgNoticeBoardBundle:Post')
            ->findOneBy(array('id' => $id))
        ;

        if (null === $post) {
            throw new TransformationFailedException(sprintf(
                'A post with id "%s" does not exist!',
                $id
            ));
        }

        return $post;
    }
}

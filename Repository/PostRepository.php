<?php

namespace Bkstg\NoticeBoardBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Bkstg\NoticeBoardBundle\Entity\Post;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllActive(Production $production)
    {
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->join('p.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->andWhere($qb->expr()->isNull('p.parent'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('p.expiry'),
                $qb->expr()->gt('p.expiry', ':now')
            ))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('status', Post::STATUS_ACTIVE)
            ->setParameter('now', new \DateTime())

            // Order by and get results.
            ->orderBy('p.pinned', 'DESC')
            ->addOrderBy('p.created', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

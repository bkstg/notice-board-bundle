<?php

declare(strict_types=1);

/*
 * This file is part of the BkstgNoticeBoardBundle package.
 * (c) Luke Bainbridge <http://www.lukebainbridge.ca/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bkstg\NoticeBoardBundle\Repository;

use Bkstg\CoreBundle\Entity\Production;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class PostRepository extends EntityRepository
{
    /**
     * Find all active posts.
     *
     * @param Production $production The production to search for.
     *
     * @return Collection
     */
    public function findAllActive(Production $production): Collection
    {
        return $this->getAllActiveQuery($production)->getResult();
    }

    /**
     * Find all inactive posts.
     *
     * @param Production $production The production to search for.
     *
     * @return Collection
     */
    public function findAllInactive(Production $production): Collection
    {
        return $this->getAllInactiveQuery($production)->getResult();
    }

    /**
     * Query to find all active posts.
     *
     * @param Production $production The production to search for.
     *
     * @return Query
     */
    public function getAllActiveQuery(Production $production): Query
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->join('p.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->eq('p.active', ':active'))
            ->andWhere($qb->expr()->isNull('p.parent'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('p.expiry'),
                $qb->expr()->gt('p.expiry', ':now')
            ))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())

            // Order by and get results.
            ->orderBy('p.pinned', 'DESC')
            ->addOrderBy('p.created', 'DESC')
            ->getQuery();
    }

    /**
     * Query to find all inactive posts.
     *
     * @param Production $production The production to search for.
     *
     * @return Query
     */
    public function getAllInactiveQuery(Production $production): Query
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->join('p.groups', 'g')

            // Add conditions.
            ->andWhere($qb->expr()->eq('g', ':group'))
            ->andWhere($qb->expr()->isNull('p.parent'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('p.active', ':active'),
                $qb->expr()->lt('p.expiry', ':now')
            ))

            // Add parameters.
            ->setParameter('group', $production)
            ->setParameter('active', false)
            ->setParameter('now', new \DateTime())

            // Order by and get results.
            ->addOrderBy('p.updated', 'DESC')
            ->getQuery();
    }
}

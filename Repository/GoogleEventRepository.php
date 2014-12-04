<?php

/**
 * @package Newscoop\GoogleEventsPluginBundle
 * @author Mark Lewis <mark.lewis@sourcefabric.org>
 */

namespace Newscoop\GoogleEventsPluginBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Newscoop\GoogleEventsPluginBundle\TemplateList\GoogleEventCriteria;
use Newscoop\ListResult;

/**
 * GoogleEventRepository
 */
class GoogleEventRepository extends EntityRepository
{
    /**
     * Get list for given criteria
     *
     * @param Newscoop\GoogleEventsPluginBundle\TemplateList\GoogleEventCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(GoogleEventCriteria $criteria, $showResults = true)
    {
        $qb = $this->createQueryBuilder('a');
        $list = new ListResult();

        $qb->select('a');

        if (!empty($criteria->status)) {
            if (count($criteria->status) > 1) {
                $qb->andWhere($qb->expr()->orX('a.isActive = true', 'a.isActive = false'));
            } else {
                $qb->andWhere('a.isActive = :status');
                $qb->setParameter('status', $criteria->status[0] == 'true' ? true : false);
            }
        }

        if ($criteria->query) {
            $qb->andWhere($qb->expr()->orX(
                "(a.description LIKE :query)", 
                "(a.creatorEmail LIKE :query)", 
                "(a.creatorDisplayName LIKE :query)", 
                "(a.location LIKE :query)"
            ));
            $qb->setParameter('query', '%' . trim($criteria->query, '%') . '%');
        }

        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('a.'.$key.' '.$operator.' :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $countQb = clone $qb;
        $list->count = (int) $countQb->select('COUNT(DISTINCT a)')->getQuery()->getSingleScalarResult();

        if ($criteria->firstResult != 0) {
            $qb->setFirstResult($criteria->firstResult);
        }

        if ($criteria->maxResults != 0) {
            $qb->setMaxResults($criteria->maxResults);
        }

        $metadata = $this->getClassMetadata();
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 'a.' . $key;
            }

            $qb->orderBy($key, $order);
        }

        if (!$showResults) {
            return $qb->getQuery();
        }

        $list->items = $qb->getQuery()->getResult();

        return $list;
    }

    /**
     * Get GoogleEvent count for given criteria
     *
     * @param  array $criteria
     * @return int
     */
    public function countBy(array $criteria = array())
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(a)')
            ->from($this->getEntityName(), 'a');

        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $queryBuilder->andWhere("a.$property = :$property");
            }
        }

        $query = $queryBuilder->getQuery();
        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $query->setParameter($property, $value);
            }
        }

        return (int) $query->getSingleScalarResult();
    }

    /**
     * Delete events with end times in the past
     *
     * @return boolean
     */
    public function deleteOldEvents()
    {
        $deleted = false;
        $query = $this->getEntityManager()->createQueryBuilder()
            ->delete($this->getEntityName(), 'e')
            ->where('e.end < :now')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery();
        $deleted = $query->execute();
        return $deleted;
    }
}

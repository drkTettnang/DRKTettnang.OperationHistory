<?php
namespace DRKTettnang\OperationHistory\Domain\Repository;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @Flow\Scope("singleton")
 */
class OperationRepository extends Repository
{
   /**
     * @Flow\Inject
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
   protected $entityManager;

   protected $defaultOrderings = array('date' => 'DESC');

   public function findYears() {

      /** @var QueryBuilder $queryBuilder */
      $queryBuilder = $this->entityManager->createQueryBuilder();
      $queryBuilder
            ->select('o.year')
            ->distinct()
            ->from($this->getEntityClassName(), 'o')
            ->orderBy('o.date', 'DESC');
      $result = $queryBuilder->getQuery()->getResult();

      return array_map(function ($y){
         return $y['year'];
      }, $result);
   }

   public function findLastYear() {

      /** @var QueryBuilder $queryBuilder */
      $queryBuilder = $this->entityManager->createQueryBuilder();
      $queryBuilder
            ->select('o.year')
            ->distinct()
            ->from($this->getEntityClassName(), 'o')
            ->orderBy('o.date', 'DESC');
      $result = $queryBuilder->getQuery()->getResult();

      return (count($result) > 0) ? $result[0]['year'] : date('Y');
   }

   public function getNumber(\DRKTettnang\OperationHistory\Domain\Model\Operation $operation) {
      $year = $operation->getYear();
      $date = $operation->getDate();

      /** @var QueryBuilder $queryBuilder */
      $queryBuilder = $this->entityManager->createQueryBuilder();
      $queryBuilder
         ->select('count(o)')
         ->from($this->getEntityClassName(), 'o')
         ->where('o.year=:year AND o.date < :date');
      $queryBuilder->setParameter('year', $year);
      $queryBuilder->setParameter('date', $date);

      $query = $queryBuilder->getQuery();

      return $query->getSingleScalarResult() + 1;
   }

   public function findByYearAndNumber($year, $number) {
      /** @var QueryBuilder $queryBuilder */
      $queryBuilder = $this->entityManager->createQueryBuilder();
      $queryBuilder
            ->select('o')
            ->from($this->getEntityClassName(), 'o')
            ->where('o.year = :year')
            ->orderBy('o.date', 'ASC');
      $queryBuilder->setParameter('year', $year);
      $result = $queryBuilder->getQuery()->getResult();

      return (count($result) >= $number)?$result[$number - 1]:null;
   }

   public function findByYear($year) {
      $query = $this->createQuery();

      return
         $query->matching(
            $query->equals('year', $year)
         )
         ->setOrderings(array('date' =>  \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
         ->execute();
   }

   public function findLatest() {

      $query = $this->createQuery();
      $query
         ->setOrderings(array('date' =>  \Neos\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
         ->setLimit(1);

      return $query->execute()->getFirst();
   }

   public function getTypeStatisticByYear($year) {

      $rsm = new ResultSetMapping();
      $rsm->addScalarResult('count', 'count');
      $rsm->addScalarResult('label', 'label');

      $queryString = 'SELECT label, count(*) as count FROM `drktettnang_operationhistory_domain_model_operationtype` t JOIN drktettnang_operationhistory_domain_model_operation o ON o.type = t.persistence_object_identifier WHERE o.year = :year GROUP BY o.type';

      $query = $this->entityManager->createNativeQuery($queryString, $rsm);
      $query->setParameter('year', $year);

      return $query->getResult();
   }

   public function getOldOperations($start, $limit) {
      $rsm = new ResultSetMapping();
      $rsm->addScalarResult('properties', 'properties');

      $query = $this->entityManager->createNativeQuery('SELECT properties FROM typo3_typo3cr_domain_model_nodedata WHERE nodetype = ? LIMIT ?, ?', $rsm);
      $query->setParameter(1, 'DRKTettnang.Homepage:Operation');
      $query->setParameter(2, $start);
      $query->setParameter(3, $limit);

      $result = $query->getResult();

      return array_map(function ($r){
         return json_decode($r['properties']);
      }, $result);
   }
}

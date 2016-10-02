<?php
namespace DRKTettnang\OperationHistory\Domain\Repository;

/*
 * This file is part of the DRKTettnang.OperationHistory package.
 */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\Repository;
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
   
   public function findByYear($year, $limit = null, $offset = 0) {
      /** @var QueryBuilder $queryBuilder */
      $queryBuilder = $this->entityManager->createQueryBuilder();
      $queryBuilder
            ->select('o')
            ->from($this->getEntityClassName(), 'o')
            ->where('o.year = :year')
            ->orderBy('o.date', 'DESC');

      if (is_numeric($limit)) {
         $queryBuilder->setMaxResults($limit);
      }
      $queryBuilder->setFirstResult($offset);
      
      $queryBuilder->setParameter('year', $year);
      
      $query = $this->createQuery();
                return
                        $query->matching(
                                $query->equals('year', $year)
                        )
                        ->setOrderings(array('date' =>  \TYPO3\Flow\Persistence\QueryInterface::ORDER_DESCENDING))
                        ->execute();
      
      $result = $queryBuilder->getQuery()->getResult();
      
      return $result;
   }
}

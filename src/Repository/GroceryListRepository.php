<?php

namespace App\Repository;

use App\Entity\GroceryList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<GroceryList>
 */
class GroceryListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, GroceryList::class);
    }

    public function paginateLists(int $page) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('r');
        $perPage = 2;

        return $this->paginator->paginate($queryBuilder,$page,$perPage,[
            'distinct' => true,
            'sortFieldAllowList' => [
                'r.id','r.title','r.slug'
            ],
        ]);
    }

    //    /**
    //     * @return GroceryList[] Returns an array of GroceryList objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GroceryList
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

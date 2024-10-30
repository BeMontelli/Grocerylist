<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Product::class);
    }

    public function paginateUserProducts(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('i')
            ->andWhere('i.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('i.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPage(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'i.id','i.title','i.slug'
            ],
        ]);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

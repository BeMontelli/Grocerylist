<?php

namespace App\Repository;

use App\Entity\GroceryList;
use App\Entity\GroceryListIngredient;
use App\Entity\User;
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
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;
    private $paginator;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        parent::__construct($registry, GroceryList::class);
    }

    public function paginateUserLists(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('l')
            ->andWhere('l.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPage(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'l.id','l.title','l.slug'
            ],
        ]);
    }

    public function getIngredientGroceryLists($ingredient) {
        return $this->entityManager
        ->getRepository(GroceryList::class)
        ->createQueryBuilder('g')
        ->join(GroceryListIngredient::class, 'gli', 'WITH', 'gli.groceryList = g')
        ->where('gli.ingredient = :ingredient')
        ->setParameter('ingredient', $ingredient)
        ->getQuery()
        ->getResult();
    }

    public function findAllForUser($user)
    {
        return $this->createQueryBuilder('g')
        ->where('g.user = :val')
        ->setParameter('val', $user->getId())
        ->orderBy('g.title', 'ASC');
    }

    /*public function findWithIngredients($id)
    {
        return $this->createQueryBuilder('gl')
            ->leftJoin('gl.ingredients', 'i')
            ->addSelect('i')
            ->leftJoin('i.section', 's')
            ->addSelect('s')
            ->where('gl.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }*/

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

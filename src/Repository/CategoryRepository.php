<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Category::class);
    }

    public function paginateUserCategoriesWithRecipesTotal(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('c')
            ->select('c as category', 'COUNT(r.id) as total')
            ->leftJoin('c.recipes', 'r')
            ->andWhere('c.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('c.position', 'ASC')
            ->groupBy('c.id')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPage(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'c.id','c.title','c.slug'
            ],
        ]);
    }

    public function findAllByUser(User $user,$exec = false) {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('c.position', 'ASC');

        if($exec) {
            return $query
            ->getQuery()
            ->getResult();
        } else return $query;
    }

    public function findForUser(User $user)
    {
        return $this->createQueryBuilder('i')
                    ->where('i.user = :val')
                    ->orderBy('i.position', 'ASC')
                    ->setParameter('val', $user->getId());
    }

    public function updatePositions(array $ids,User $user)
    {
        foreach ($ids as $position => $id) {
            $this->createQueryBuilder('s')
                ->update()
                ->set('s.position', ':position')
                ->where('s.id = :id')
                ->andWhere('s.user = :user')
                ->setParameter('position', $position)
                ->setParameter('id', $id)
                ->setParameter('user', $user)
                ->getQuery()
                ->execute();
        }
    }

    //    /**
    //     * @return Recipe[] Returns an array of Recipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recipe
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

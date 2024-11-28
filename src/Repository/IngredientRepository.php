<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
class IngredientRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Ingredient::class);
    }

    public function paginateUserIngredients(int $page, User $user) : PaginationInterface {

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

    public function paginateUserSearchedIngredients(int $page, User $user, ?string $title, array $sections) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('i')
            ->andWhere('i.user = :val')
            ->setParameter('val', $user->getId());

        if ($title !== null) {
            $queryBuilder->andWhere('i.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if (!empty($sections)) {
           $queryBuilder->andWhere('i.section IN (:sections)')
                ->setParameter('sections', $sections);
        }

        $queryBuilder->orderBy('i.id', 'ASC');
        $query = $queryBuilder->getQuery()->getResult();

        return $this->paginator->paginate($query,$page,self::getPerPage(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'i.id','i.title','i.slug'
            ],
        ]);
    }

    public function findAvailableForRecipeAndUser(User $user)
    {
        return $this->createQueryBuilder('i')
                    ->andWhere('i.user = :val')
                    ->setParameter('val', $user->getId())
                    ->andWhere('i.availableRecipe = :available')
                    ->setParameter('available', true);
    }

    //    /**
    //     * @return Ingredient[] Returns an array of Ingredient objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ingredient
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

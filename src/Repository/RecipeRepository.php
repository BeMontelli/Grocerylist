<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Recipe::class);
    }

    public function paginateUserRecipes(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('r')
            ->andWhere('r.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPageGrid(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'r.id','r.title','r.slug'
            ],
        ]);
    }

    public function paginateUserSearchedRecipes(int $page, User $user, ?string $title, array $categories) : PaginationInterface {
        
        $queryBuilder = $this->createQueryBuilder('r')
            ->andWhere('r.user = :val')
            ->setParameter('val', $user->getId());

        if ($title !== null) {
            $queryBuilder->andWhere('r.title LIKE :title')
                ->setParameter('title', '%' . $title . '%');
        }

        if (!empty($categories)) {
            $queryBuilder->andWhere('r.category IN (:categories)')
                ->setParameter('categories', $categories);
        }

        $queryBuilder->orderBy('r.id', 'ASC');
        $query = $queryBuilder->getQuery()->getResult();

        return $this->paginator->paginate($query,$page,self::getPerPageGrid(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'r.id','r.title','r.slug'
            ],
        ]);
    }

    /*public function paginateRecipesWithCategories(int $page, int $limit): Paginator {
        $queryBuilder = $this->createQueryBuilder('r')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->setHint(Paginator::HINT_ENABLE_DISTINCT, false);

        return new Paginator($queryBuilder,false);
    }*/

    public function findAllRecipesWithCategories()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('r', 'c')
            ->from(Recipe::class, 'r')
            ->leftJoin('r.category', 'c');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByUser(User $user) {

        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('c.id', 'ASC');
    }

    public function findRecipeWithCategory($recipeId)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('r', 'c')
            ->from(Recipe::class, 'r')
            ->leftJoin('r.category', 'c')
            ->where('r.id = :recipeId')
            ->setParameter('recipeId', $recipeId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
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

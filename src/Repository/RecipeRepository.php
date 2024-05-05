<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Recipe::class);
    }

    public function findAllRecipesWithCategories()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('r', 'c')
            ->from(Recipe::class, 'r')
            ->leftJoin('r.category', 'c');

        return $queryBuilder->getQuery()->getResult();
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

<?php

namespace App\Repository;

use App\Entity\Ingredient;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @extends ServiceEntityRepository<Ingredient>
 */
class SearchRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $em */
    private $em;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator)
    {
        $this->em = $em;
        $this->urlGenerator = $urlGenerator;
    }

    public function search(string $title, int $userId): array
    {
        $conn = $this->em->getConnection();

        $sql = "
            SELECT * FROM (
                SELECT 'recipe' AS type, r.id, r.title, r.slug, r.user_id
                FROM recipe r
                WHERE r.title LIKE :title AND r.user_id = :userId
                LIMIT 30
            ) AS recipes
            
            UNION ALL
            
            SELECT * FROM (
                SELECT 'ingredient' AS type, i.id, i.title, i.slug, i.user_id
                FROM ingredient i
                WHERE i.title LIKE :title AND i.user_id = :userId
                LIMIT 30
            ) AS ingredients
        ";

        $resultSet = $conn->executeQuery($sql, [
            'title' => '%' . $title . '%',
            'userId' => $userId,
        ]);

        $results = $resultSet->fetchAllAssociative();

        foreach ($results as &$result) {
            if ($result['type'] === 'recipe') {
                $result['path'] = $this->urlGenerator->generate(
                    'admin.recipe.show', 
                    ['id' => $result['id'],'slug' => $result['slug']],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } elseif ($result['type'] === 'ingredient') {
                $result['path'] = $this->urlGenerator->generate(
                    'admin.ingredient.show', 
                    ['id' => $result['id'],'slug' => $result['slug']],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            }
        }

        return $results;
    }
}

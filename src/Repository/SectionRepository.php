<?php

namespace App\Repository;

use App\Entity\Section;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, private PaginatorInterface $paginator)
    {
        $this->entityManager = $entityManager;
        parent::__construct($registry, Section::class);
    }

    public function paginateUserSections(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('s')
            ->andWhere('s.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('s.position', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPage(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                's.id','s.title','s.slug'
            ],
        ]);
    }

    public function findAllByUser(User $user) {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('s.position', 'ASC');
    }

    public function findForUser(User $user)
    {
        return $this->createQueryBuilder('s')
                    ->where('s.user = :val')
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
    //     * @return Section[] Returns an array of Section objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Section
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

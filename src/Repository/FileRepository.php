<?php

namespace App\Repository;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    use ConfigRepositoryTrait;
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
        parent::__construct($registry, File::class);
    }

    public function findAllByUser(User $user) {

        $queryBuilder = $this->createQueryBuilder('s')
            ->andWhere('s.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('s.id', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    public function paginateUserFiles(int $page, User $user) : PaginationInterface {

        $queryBuilder = $this->createQueryBuilder('l')
            ->andWhere('l.user = :val')
            ->setParameter('val', $user->getId())
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->paginator->paginate($queryBuilder,$page,self::getPerPageGrid(),[
            'distinct' => true,
            'sortFieldAllowList' => [
                'l.id','l.title'
            ],
        ]);
    }

    public function findForUser(User $user)
    {
        return $this->createQueryBuilder('i')
                    ->where('i.user = :val')
                    ->setParameter('val', $user->getId());
    }

    //    /**
    //     * @return File[] Returns an array of File objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?File
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

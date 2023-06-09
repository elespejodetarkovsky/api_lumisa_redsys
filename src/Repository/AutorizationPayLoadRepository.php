<?php

namespace App\Repository;

use App\Entity\AutorizationPayLoad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AutorizationPayLoad>
 *
 * @method AutorizationPayLoad|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutorizationPayLoad|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutorizationPayLoad[]    findAll()
 * @method AutorizationPayLoad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutorizationPayLoadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AutorizationPayLoad::class);
    }

    public function save(AutorizationPayLoad $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AutorizationPayLoad $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ApiToken[] Returns an array of ApiToken objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ApiToken
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

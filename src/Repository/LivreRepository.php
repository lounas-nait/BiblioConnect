<?php

namespace App\Repository;

use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livre>
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    public function save(Livre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Livre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Livre[]
     */
    public function search(?string $term, ?Categorie $category = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.auteur', 'a')
            ->leftJoin('l.categories', 'c')
            ->leftJoin('l.langue', 'lang')
            ->addSelect('a')
            ->addSelect('c')
            ->addSelect('lang');

        if ($term) {
            $qb->andWhere('l.titre LIKE :term OR a.nom LIKE :term OR c.nom LIKE :term')
                ->setParameter('term', '%' . $term . '%');
        }

        if ($category) {
            $qb->andWhere(':category MEMBER OF l.categories')
                ->setParameter('category', $category);
        }

        return $qb->orderBy('l.titre', 'ASC')->getQuery()->getResult();
    }
}

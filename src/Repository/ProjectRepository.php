<?php

namespace App\Repository;

use App\Entity\Project;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProjectRepository extends Repository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @return Project[]
     */
    public function findAll()
    {
        return $this->findBy([], ['lastTickAt' => 'DESC', 'name' => 'ASC']);
    }

    /**
     * @return Project[]
     */
    public function findAllWithTickers()
    {
        return $this->createQueryBuilder('p')
            ->join(
                'App:Ticker',
                'ticker',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'ticker.project = p.id'
            )->orderBy('p.lastTickAt', 'DESC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneById(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findOneRecent()
    {
        return $this->findOneBy([], ['lastTickAt' => 'DESC']);
    }
}

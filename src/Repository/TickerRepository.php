<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Ticker;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TickerRepository extends Repository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ticker::class);
    }

    /**
     * @return Ticker[]
     */
    public function findAll()
    {
        return $this->findBy([], ['lastTickAt' => 'DESC']);
    }

    /**
     * @param Project $project
     * @return Ticker[]
     */
    public function findByProject(Project $project)
    {
        return $this->findBy(['project' => $project], ['lastTickAt' => 'DESC']);
    }

    /**
     * @return Ticker
     */
    public function findOneCurrent()
    {
        return $this->findOneBy(['current' => true]);
    }

    /**
     * @return Ticker
     */
    public function clearCurrent()
    {
        $this->createQueryBuilder('t')
            ->update('App\Entity\Ticker', 't')
            ->set('t.current', ':current')
            ->setParameter('current', false)
            ->getQuery()
            ->getResult();
    }
}

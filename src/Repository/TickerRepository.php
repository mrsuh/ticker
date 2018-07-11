<?php

namespace App\Repository;

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
        return $this->findBy([], ['lastTickAt' => 'DESC', 'usageCount' => 'ASC', 'project' => 'ASC']);
    }

    /**
     * @param int $category
     * @return Ticker[]
     */
    public function findByCategory(int $category)
    {
        return $this->findBy(['category' => $category], ['lastTickAt' => 'DESC', 'usageCount' => 'ASC', 'project' => 'ASC']);
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

<?php

namespace App\Repository;

use App\Entity\TimeLine;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TimeLineRepository extends Repository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TimeLine::class);
    }
}

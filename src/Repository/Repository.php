<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class Repository extends ServiceEntityRepository
{
    public function create($object)
    {
        $this->_em->persist($object);
        $this->_em->flush($object);

        return $object;
    }

    public function update($object)
    {
        $this->_em->flush($object);

        return $object;
    }
}

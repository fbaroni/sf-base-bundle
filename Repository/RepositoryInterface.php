<?php
namespace AppBundle\Repository;

interface RepositoryInterface
{
    public function getEntityName();
    public function getEntity($id, $type);
    public function getAllEntities($type);
    public function find($idEntidad);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function findOneBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function save($object);
    public function remove($object);

}
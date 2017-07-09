<?php

namespace Fbaroni\Bundle\BaseBundle\Repository;

use AppBundle\Repository\RepositoryInterface;
use Doctrine\ORM\EntityManager;

abstract class DoctrineEntityRepository implements RepositoryInterface
{
    /*
     * Inyección de dependencias
     */
    protected $entityManager;

    /**
     * DoctrineEntityRepository constructor.
     *
     * @param $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    abstract protected function getEntityName();

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getDoctrineRepository()
    {
        return $this->getRepositoryFromEntityManager($this->getEntityName());
    }
    /*
     * Fin Inyección de dependencias
     */

    /**
     * @param $entity
     * @param null $bundle
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepositoryFromEntityManager($entity, $bundle = null)
    {
        return $this->getEntityManager()->getRepository(($bundle ? $bundle : 'AppBundle').':'.$entity);
    }

    public function getEntity($id, $type)
    {
        return $this->getRepositoryFromEntityManager($type)->find($id);
    }

    public function getAllEntities($type)
    {
        return $this->getRepositoryFromEntityManager($type)->findAll();
    }

    /**
     * @param $dql
     *
     * @return array
     */
    public function getDQLQueryResult($dql)
    {
        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getResult();
    }

    public function executeSQLQuery($query, $params = array())
    {
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetchAll();
    }

    public function executeOneResultSQLQuery($query, $params = array())
    {
        return $this->getEntityManager()->getConnection()->executeQuery($query, $params)->fetch();
    }

    public function find($idEntidad)
    {
        try {
            if (empty($idEntidad)) {
                return null;
            }

            return $this->getDoctrineRepository()->find($idEntidad);

        } catch (\Exception $ex) {
            //            throw new AppErrorException('Error el buscar ' . $tipo);
        }

        return null;
    }

    public function findAll()
    {
        return $this->getDoctrineRepository()->findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getDoctrineRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }
    public function findOneBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getDoctrineRepository()->findOneBy($criteria, $orderBy, $limit, $offset);
    }

    public function save($object)
    {
        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();

        return true;
    }

    public function remove($object)
    {
        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();

        return true;
    }

    protected function commit()
    {
        $this->getEntityManager()->flush();
    }
    protected function begin()
    {
        $this->getEntityManager()->getConnection()->beginTransaction();
    }
    protected function persist($entity)
    {
        $this->getEntityManager()->persist($entity);
    }
}

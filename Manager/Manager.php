<?php

namespace Fbaroni\Bundle\BaseBundle\Manager;

use Fbaroni\Bundle\BaseBundle\Repository\DoctrineEntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class Manager
{
    protected $eventDispatcher;
    protected $logger;
    protected $securityTokenStorage;
    protected $session;
    protected $paginator;
    protected $repositorio;

    /**
     * Manager constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param $logger
     * @param TokenStorageInterface $securityTokenStorage
     * @param Session $session
     * @param $paginator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher,
                                $logger,
                                TokenStorageInterface $securityTokenStorage,
                                Session $session,
                                $paginator)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->session = $session;
        $this->paginator = $paginator;
    }

    public function saveEntity($entidad)
    {
        return $this->getRepositorio()->save($entidad);
    }

    public function removeEntity($entidad)
    {
        return $this->getRepositorio()->remove($entidad);
    }

    public function getEntity($idEntidad, $tipo)
    {
        return $this->getRepositorio()->getEntity($idEntidad, $tipo);
    }

    public function getAll($tipo)
    {
        return $this->getRepositorio()->getAllEntities($tipo);
    }

    public function convertToSimpleArrayFromEntityCollection($entitiesCollection)
    {
        $simpleArray = array();

        foreach ($entitiesCollection as $entity) {
            $simpleArray [] = array('id' => $entity->getId(), 'label' => $entity->getNombre());
        }

        return $simpleArray;
    }

    protected function getUser()
    {
        return $this->getSecurityTokenStorage()->getToken()->getUser();
    }

    /**
     * @param \Exception $ex
     */
    protected function logError(\Exception $ex)
    {
        try {
            $method = '';
            $class = '';
            $callers = debug_backtrace();

            if (array_key_exists(1, $callers)) {
                $method = array_key_exists('function', $callers[1]) ? $callers[1]['function'] : '';
                $class = array_key_exists('class', $callers[1]) ? $callers[1]['class'] : '';
            }
            $this->logger->error('(' . $class . ' - ' . $method . '): ' . $ex->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($ex->getMessage());
        }
    }

    public function getPaginacion($entidades, $pagina, $numeroPaginas = 10)
    {
        return $this->getPaginator()->paginate(
            $entidades, $pagina, $numeroPaginas
        );
    }

    /*
     * INYECCION DE DEPENDENCIAS
     */

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return TokenStorageInterface
     */
    public function getSecurityTokenStorage()
    {
        return $this->securityTokenStorage;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return mixed
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @return DoctrineEntityRepository
     */
    public function getRepositorio()
    {
        return $this->repositorio;
    }
}

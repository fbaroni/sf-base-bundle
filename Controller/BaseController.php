<?php

namespace Fbaroni\Bundle\BaseBundle\Controller;

use Fbaroni\Bundle\BaseBundle\Form\Handler\FormHandler;
use Fbaroni\Bundle\BaseBundle\Manager\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller
{
    /**
     * @return Manager
     */
    abstract protected function getManager();

    /**
     * @return FormHandler
     */
    abstract protected function getFormHandler();

    abstract protected function getIndexRoute();

    protected function setMensajeFlashError($mensaje)
    {
        $this->get('session')->getFlashBag()->add(
                'error', $mensaje
        );
    }

    protected function getMensajeFlash($clave)
    {
        $mensajeFlash = $this->get('session')->getFlashBag()->get($clave);

        return $mensajeFlash ? $mensajeFlash[0] : '';
    }

    protected function setMensajeFlashExito($mensaje)
    {
        $this->get('session')->getFlashBag()->add(
                'success', $mensaje
        );
    }

    public function setMensajeFlash($clave, $mensaje)
    {
        $this->get('session')->getFlashBag()->add($clave, $mensaje);
    }

    protected function redirectToIndex()
    {
        return $this->redirectToRoute($this->getIndexRoute());
    }

    protected function guardarEntidad($entidad)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entidad);
        $em->flush();
    }

    protected function getEntidad($entidad, $id)
    {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository("AppBundle:$entidad")->find($id);
    }

    protected function logError($mensaje = '')
    {
        $this->get('logger')->error($mensaje);
    }

    protected function logInfo($mensaje = '')
    {
        $this->get('logger')->info($mensaje);
    }

    protected function logWarning($mensaje = '')
    {
        $this->get('logger')->warning($mensaje);
    }

    /**
     * @return bool
     */
    protected function checkUserLogged()
    {
        if (!$this->get('security.token_storage')->getToken() ||
                $this->obtenerUsuarioLogueado() == 'anon.'
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param $array
     *
     * @return Response
     */
    protected function returnJSONResponseFromArray($array)
    {
        $jsonData = json_encode($array);

        $response = new Response();
        $response->setContent($jsonData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @param $status
     * @param $data
     * @param string $message
     *
     * @return Response
     */
    protected function returnJSONStandardResponseFromArray($status, $data, $message = '')
    {
        $jsonData = json_encode(
                [
                    'status' => $status,
                    'data' => $data,
                    'message' => $message,
                ]
        );

        $response = new Response();
        $response->setContent($jsonData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function hasRole($rol)
    {
        return $this->get('security.authorization_checker')->isGranted($rol);
    }
}



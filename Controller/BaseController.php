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

    protected function redirectToIndexRoute(array $parameters = array(), $status = 302)
    {
        return $this->redirectToRoute($this->getIndexRoute(), $parameters, $status);
    }

    /**
     * @param Request $request
     * @param $prefix
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndexRouteWithFilters(Request $request)
    {
        $prefix = $this->getIndexRoute();
        //Redireccionar al listado ordenado en caso de que en sesion este guardado un orden y no haya sido seteado
        // uno nuevo en request
        $sessionSortKey = $prefix . 'sort';
        $sessionDirectionKey = $prefix . 'direction';
        if ($request->get('sort') == '' && $this->get('session')->get($sessionSortKey) != '') {
            return $this->redirectToIndexRoute([
                'request' => $request,
                'sort' => $this->get('session')->get($sessionSortKey),
                'direction' => $this->get('session')->get($sessionDirectionKey)
            ], 307);
        } elseif ($request->get('sort') != '') {
            $this->get('session')->set($sessionDirectionKey, $request->get('direction'));
            $this->get('session')->set($sessionSortKey, $request->get('sort'));
        }
        return false;
    }

    protected function generateJSONErrorResponse($message = null)
    {
        $status = 'error';
        $mensaje = $message ? $message : 'Ocurrió un error.';
        $data = [];
        return $this->returnJSONStandardResponseFromArray($status, $data, $mensaje);
    }

    protected function generateJSONResponse($data, $mensaje = '')
    {
        return $this->returnJSONStandardResponseFromArray('OK', $data, $mensaje);
    }

    public function ajaxSetVariableSesionAction($nombre, $valor)
    {
        try {
            if ($this->get('request')->isXmlHttpRequest()) {
                $this->get('request')->getSession()->set($nombre, $valor);
                return $this->generateJSONResponse([], 'Se cargo la variable correctamente.');
            }
        } catch (\Exception $exception) {
            return $this->generateJSONErrorResponse();
        }
    }

    protected function obtenerClaveFiltroSesion()
    {
        return $this->getIndexRoute() . 'buscar';
    }

    protected function obtenerTerminoABuscar($terminoBuscado)
    {
        $filtroSessionKey = $this->obtenerClaveFiltroSesion();
        if ($terminoBuscado != '') {
            $this->get('session')->set($filtroSessionKey, $terminoBuscado);
            return $terminoBuscado;
        } elseif ($this->get('session')->get($filtroSessionKey) != '') {
            $this->limpiarElementoFiltroSesion($terminoBuscado, $filtroSessionKey);
            return $this->get('session')->get($filtroSessionKey);
        } else {
            return '';
        }
    }

    protected function limpiarElementoFiltroSesion($terminoBuscado, $filtroSessionKey)
    {
    //El elemento llega vacío lo elimino como filtro
        if (($terminoBuscado == '' || $terminoBuscado == 0) && $this->get('request')->get('send')) {
            $this->get('session')->set($filtroSessionKey, $terminoBuscado);
            $filtro = $terminoBuscado;
        }
    }
}



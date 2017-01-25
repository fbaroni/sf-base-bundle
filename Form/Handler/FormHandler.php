<?php

namespace Fbaroni\Bundle\BaseBundle\Form\Handler;

use AppBundle\Manager\Manager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class FormHandler
{
    protected $request;
    protected $validator;
    protected $manager;

    /**
     * @param Form $form
     * @param $entidad
     * @param Request $request
     *
     * @return bool
     */
    public function handleCreacionBasica(Form $form, $entidad, Request $request)
    {
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getManager()->saveEntity($entidad);

                return true;
            }
        }

        return false;
    }

    public function validarConstraintsYObtenerMensajes($valor, $constraints)
    {
        $errors = array();
        foreach ($constraints as $constraint) {
            $errors [] = $this->validator->validateValue(
                $valor, $constraint
            );
        }
        $errores = '';
        foreach ($errors as $error) {
            foreach ($error as $contrainstViolation) {
                $errores .= $contrainstViolation->getMessage().' ';
            }
        }

        return $errores;
    }

    public function getFormErrores($form)
    {
        $errores = array();

        foreach ($form as $fieldName => $formField) {
            foreach ($formField->getErrors(true) as $error) {
                $errores[] = $error->getMessage();
            }
        }

        return $errores;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @param mixed $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param mixed $manager
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
    }
}

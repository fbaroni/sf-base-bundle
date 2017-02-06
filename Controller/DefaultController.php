<?php

namespace Fbaroni\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FbaroniBaseBundle:Default:index.html.twig');
    }
}

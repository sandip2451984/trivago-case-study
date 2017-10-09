<?php

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class GUIController extends Controller {

    public function indexAction(Request $request) : Response {
        return $this->render('default/index.html.twig', [
            'httpHost' => $request->getHttpHost()
        ]);
    }

}

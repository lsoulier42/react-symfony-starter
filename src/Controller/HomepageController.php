<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("PUBLIC_ACCESS")]
#[Route(path: "/")]
class HomepageController extends AbstractController
{
    /**
     * @return Response
     */
    #[Route(path: '/', name: 'homepage')]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig');
    }
}

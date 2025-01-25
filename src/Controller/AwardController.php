<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AwardController extends AbstractController
{
    #[Route('/award', name: 'award')]
    public function index(): Response
    {
        return $this->render('award/index.html.twig', [
            'controller_name' => 'AwardController',
        ]);
    }
}

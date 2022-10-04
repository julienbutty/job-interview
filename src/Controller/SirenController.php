<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SirenController extends AbstractController
{
    #[Route('/api-siren', name: 'app_api_siren')]
    public function createCase(): Response
    {
        return $this->render('base.html.twig');
    }
}
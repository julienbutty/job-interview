<?php

namespace App\Controller;

use App\Service\SugarApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    public function __construct(public SugarApiService $sugarApiService)
    {
    }
}

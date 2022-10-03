<?php

namespace App\Controller;

use App\Service\SugarApiService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use function json_decode;

class ApiController extends AbstractController
{

    public function __construct(public SugarApiService $sugarApiService)
    {
    }

    #[Route('/contact-list', name: 'app_contact_list')]
    public function showContactList(): Response
    {
        try {
            $contacts = $this->sugarApiService->findAllContacts();
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        if (empty($contacts)) {
            $contacts = 'The contact list is empty';
        }

        return $this->json([
            json_decode($contacts)
        ]);
    }
}

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

    #[Route('/specific-contact', name: 'app_specific_contact')]
    public function showSpecificContact(): Response
    {
        $contact = $this->getSpecificContact();

        if (empty($contact)) {
            throw new NotFoundHttpException('Specific user doesn\'t exist');
        }

        return $this->json([
            $contact->first_name,
            $contact->last_name,
            $contact->primary_address_street,
            $contact->primary_address_city,
            $contact->primary_address_postalcode,
            $contact->email1,
        ]);
    }

    #[Route('/specific-contact-cases', name: 'app_specific_contact_cases')]
    public function showSpecificContactCases(): Response
    {
        $contact = $this->getSpecificContact();

        if (empty($contact)) {
            throw new NotFoundHttpException('Specific user doesn\'t exist');
        }

        $contactId = $contact->id;

        $contactCases = json_decode($this->sugarApiService->getContactCases($contactId))->records;
        if (empty($contactCases)) {
            $contactCases = 'This user has no assigned cases';
        }

        return $this->json([
            $contactCases
        ]);
    }

    #[Route('/create-case', name: 'app_create_case')]
    public function createCase(): Response
    {
        $contact = $this->getSpecificContact();
        if (empty($contact)) {
            throw new NotFoundHttpException('Specific user doesn\'t exist');
        }

        try {
            $this->sugarApiService->createCase($contact->id);
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return $this->json([
            "Ticket was successfully created for $contact->full_name"
        ]);
    }

    private function getSpecificContact(): \stdClass
    {
        try {
            $contact = reset(json_decode($this->sugarApiService->findSpecificContact())->records);
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return $contact;
    }
}

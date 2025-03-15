<?php

namespace App\Controller\Api;

use App\Business\BilletwebBusiness;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/billetweb', name: 'api_billetweb')]
class BilletwebApiController extends AbstractController
{
    #[Route('/sync/pilots', name: 'sync', methods: ['POST'])]
    public function syncPilots(BilletwebBusiness $billetwebBusiness): Response
    {
        $billetwebBusiness->syncEventAttendees();

        return new Response();
    }
}
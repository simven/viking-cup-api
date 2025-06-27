<?php

namespace App\Controller\Api;

use App\Business\EmailBusiness;
use App\Dto\EmailDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/email')]
class EmailController extends AbstractController
{
    #[Route('/send', methods: ['POST'])]
    public function sendEmail(
        EmailBusiness $emailBusiness,
        #[MapRequestPayload] EmailDto $emailDto
    ): Response
    {
        $emailBusiness->sendEmail($emailDto);

        return new Response();
    }
    
}
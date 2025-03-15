<?php

namespace App\Controller\Api;

use App\Repository\SponsorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/sponsors', name: 'api_sponsors')]
class SponsorApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(SponsorRepository $sponsorRepository, SerializerInterface $serializer): JsonResponse
    {
        $sponsors = $sponsorRepository->findAll();

        return new JsonResponse(
            $serializer->serialize($sponsors, 'json', ['groups' => 'sponsor:read']),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
<?php

namespace App\Controller\Api;

use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/medias', name: 'api_medias')]
class MediaApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(MediaRepository $mediaRepository, SerializerInterface $serializer): JsonResponse
    {
        $medias = $mediaRepository->findAll();

        return new JsonResponse(
            $serializer->serialize($medias, 'json', ['groups' => 'media:read']),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
<?php

namespace App\Controller\Api;

use App\Repository\MediaFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/mediaFiles', name: 'api_media_files')]
class MediaFileApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(MediaFileRepository $mediaFileRepository, SerializerInterface $serializer): JsonResponse
    {
        $mediaFiles = $mediaFileRepository->findAll();

        return new JsonResponse(
            $serializer->serialize($mediaFiles, 'json', ['groups' => 'media:read']),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
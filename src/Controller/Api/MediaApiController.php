<?php

namespace App\Controller\Api;

use App\Business\MediaBusiness;
use App\Dto\MediaDto;
use App\Entity\Media;
use App\Repository\MediaFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/medias', name: 'api_medias')]
class MediaApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getMedias(
        MediaBusiness $mediaBusiness,
        #[MapQueryParameter] ?int $page,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $sort,
        #[MapQueryParameter] ?string $order,
        #[MapQueryParameter] ?int $eventId = null,
        #[MapQueryParameter] ?int $roundId = null,
        #[MapQueryParameter] ?bool $selected = null,
        #[MapQueryParameter] ?bool $selectedMailSent = null,
        #[MapQueryParameter] ?bool $watchBriefing = null,
        #[MapQueryParameter] ?bool $generatePass = null
    ): JsonResponse
    {
        $medias = $mediaBusiness->getMedias(
            $page ?? 1,
            $limit ?? 20, $sort,
            $order,
            $eventId,
            $roundId,
            $selected,
            $selectedMailSent,
            $watchBriefing,
            $generatePass
        );

        return $this->json($medias, Response::HTTP_OK, [], ['groups' => ['media', 'mediaRound', 'round', 'roundDetails', 'roundDetail']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function createMedia(
        MediaBusiness $mediaBusiness,
        Request $request,
        SerializerInterface $serializer,
        #[MapUploadedFile] UploadedFile $insuranceFile,
        #[MapUploadedFile] UploadedFile|array $bookFile
    ): Response
    {
        $mediaDto = $request->request->get('media');
        $mediaDto = $serializer->deserialize($mediaDto, MediaDto::class, 'json');

        $mediaBusiness->createPersonMedia($mediaDto, $insuranceFile, !empty($bookFile) ? $bookFile : null);

        return new Response();
    }

    #[Route('/{media}', name: 'delete', methods: ['DELETE'])]
    public function deleteMedia(
        MediaBusiness $mediaBusiness,
        Media $media
    ): Response
    {
        $mediaBusiness->deleteMedia($media);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/book/{media}', name: 'delete_book', methods: ['DELETE'])]
    public function deleteMediaBook(
        MediaBusiness $mediaBusiness,
        Media $media
    ): Response
    {
        $media = $mediaBusiness->deleteMediaBook($media);

        return $this->json($media, Response::HTTP_OK, [], ['groups' => ['media', 'mediaRound', 'round', 'roundDetails', 'roundDetail']]);
    }
}
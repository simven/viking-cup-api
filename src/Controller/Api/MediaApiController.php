<?php

namespace App\Controller\Api;

use App\Business\MediaBusiness;
use App\Dto\MediaDto;
use App\Dto\MediaSelectionDto;
use App\Entity\Media;
use App\Entity\Round;
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
        #[MapQueryParameter] ?int    $eventId = null,
        #[MapQueryParameter] ?int    $roundId = null,
        #[MapQueryParameter] ?string $name = null,
        #[MapQueryParameter] ?string $email = null,
        #[MapQueryParameter] ?string $phone = null,
        #[MapQueryParameter] ?bool   $selected = null,
        #[MapQueryParameter] ?bool   $selectedMailSent = null,
        #[MapQueryParameter] ?bool   $eLearningMailSent = null,
        #[MapQueryParameter] ?bool   $briefingSeen = null,
        #[MapQueryParameter] ?bool   $generatePass = null
    ): JsonResponse
    {
        $medias = $mediaBusiness->getMedias(
            $page ?? 1,
            $limit ?? 20, $sort,
            $order,
            $eventId,
            $roundId,
            $name,
            $email,
            $phone,
            $selected,
            $selectedMailSent,
            $eLearningMailSent,
            $briefingSeen,
            $generatePass
        );

        return $this->json($medias, Response::HTTP_OK, [], ['groups' => ['media', 'mediaRound', 'round', 'roundDetails', 'roundDetail', 'roundEvent', 'event']]);
    }

    #[Route('/public/{uniqueId}', name: 'get_by_uid', methods: ['GET'])]
    public function getMedia(
        MediaBusiness $mediaBusiness,
        string $uniqueId
    ): JsonResponse
    {
        $media = $mediaBusiness->getMediaByUniqueId($uniqueId);

        if (!$media) {
            return new JsonResponse(['message' => 'Media not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($media, Response::HTTP_OK, [], ['groups' => ['media', 'mediaRound', 'round', 'roundDetails', 'roundDetail', 'roundEvent', 'event']]);
    }

    #[Route('/public', name: 'create', methods: ['POST'])]
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

    #[Route('/{media}', name: 'update', methods: ['POST'])]
    public function updateMedia(
        MediaBusiness $mediaBusiness,
        Request $request,
        SerializerInterface $serializer,
        Media $media,
        #[MapUploadedFile] UploadedFile|array $insuranceFile,
        #[MapUploadedFile] UploadedFile|array $bookFile
    ): Response
    {
        $mediaDto = $request->request->get('media');
        $mediaDto = $serializer->deserialize($mediaDto, MediaDto::class, 'json');

        $mediaBusiness->updatePersonMedia($media, $mediaDto, !empty($insuranceFile) ? $insuranceFile : null, !empty($bookFile) ? $bookFile : null);

        return new Response();
    }

    #[Route('/{media}', name: 'update_selection', methods: ['PUT'])]
    public function updateMediaSelection(
        MediaBusiness $mediaBusiness,
        Media $media,
        #[MapRequestPayload] MediaSelectionDto $mediaSelectionDto
    ): Response
    {
        $mediaBusiness->updateMediaSelection($media, $mediaSelectionDto);

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

    #[Route('/public/generate-pass/{media}', name: 'generate_pass', methods: ['GET'])]
    public function generatePass(
        MediaBusiness $mediaBusiness,
        Media $media,
        #[MapQueryParameter] string $uniqueId
    ): Response
    {
        if ($media->getPerson()->getUniqueId() !== $uniqueId) {
            throw new \Exception('Unique ID does not match the media person unique ID');
        }
        $pdf = $mediaBusiness->generatePass($media);

        return new Response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'filename="pass_' . str_replace('.', '_', $uniqueId) . '.pdf"']);
    }

    #[Route('/public/briefing-seen/{media}', name: 'set_briefing_seen', methods: ['PUT'])]
    public function briefingSeen(
        MediaBusiness $mediaBusiness,
        Media $media,
        #[MapQueryParameter] string $uniqueId
    ): Response
    {
        if ($media->getPerson()->getUniqueId() !== $uniqueId) {
            throw new \Exception('Unique ID does not match the media person unique ID');
        }

        $mediaBusiness->briefingSeen($media);

        return new Response();
    }

    #[Route('/public/pass-generated/{media}', name: 'set_pass_generated', methods: ['PUT'])]
    public function passGenerated(
        MediaBusiness $mediaBusiness,
        Media $media,
        #[MapQueryParameter] string $uniqueId
    ): Response
    {
        if ($media->getPerson()->getUniqueId() !== $uniqueId) {
            throw new \Exception('Unique ID does not match the media person unique ID');
        }
        if (!$media->isBriefingSeen()) {
            throw new \Exception('Briefing must be seen before generating pass');
        }

        $mediaBusiness->passGenerated($media);

        return new Response();
    }

    #[Route('/send-selected-email/{round}', name: 'send_selected_email')]
    public function sendSelectedEmails(
        MediaBusiness $mediaBusiness,
        Round $round
    ): Response
    {
        $errors = $mediaBusiness->sendSelectedEmails($round);

        return $this->json($errors);
    }

    #[Route('/send-elearning-email/{round}', name: 'send_elearning_email')]
    public function sendELearningEmails(
        MediaBusiness $mediaBusiness,
        Round $round
    ): Response
    {
        $errors = $mediaBusiness->sendELearningEmails($round);

        return $this->json($errors);
    }
}
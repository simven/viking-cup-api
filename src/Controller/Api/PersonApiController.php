<?php

namespace App\Controller\Api;

use App\Business\MediaBusiness;
use App\Business\PersonBusiness;
use App\Dto\MediaDto;
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

#[Route('/api/persons', name: 'api_persons')]
class PersonApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function getPersons(
        PersonBusiness $personBusiness,
        #[MapQueryParameter] ?int $page,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $sort,
        #[MapQueryParameter] ?string $order,
        #[MapQueryParameter] ?string $personeType
    ): Response
    {
        $persons = $personBusiness->getPersons($page ?? 1, $limit ?? 20, $sort, $order, $personeType);

        return $this->json($persons, 200, [], ["groups" => ["person", "personPersonType", "personType", "personMedia", "media"]]);
    }
}
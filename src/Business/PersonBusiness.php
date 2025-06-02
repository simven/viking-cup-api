<?php

namespace App\Business;

use App\Dto\EmailDto;
use App\Dto\MediaDto;
use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Round;
use App\Helper\FileHelper;
use App\Repository\LinkTypeRepository;
use App\Repository\MediaRepository;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use App\Repository\RoundDetailRepository;
use App\Repository\RoundRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class PersonBusiness
{
    public function __construct(
        private PersonTypeRepository $personTypeRepository,
        private PersonRepository $personRepository,
        private LinkTypeRepository $linkTypeRepository,
        private MediaRepository $mediaRepository,
        private RoundRepository $roundRepository,
        private RoundDetailRepository $roundDetailRepository,
        private FileHelper $fileHelper,
        private EmailBusiness $emailBusiness,
        private EntityManagerInterface $em
    )
    {}

    public function getPersons(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?string $personType = null
    ): array
    {
        $persons = $this->personRepository->findAllPaginated($sort, $order, $personType);

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        return [
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'persons' => $persons
        ];
    }
}
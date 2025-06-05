<?php

namespace App\Business;

use App\Dto\MediaDto;
use App\Dto\MediaSelectionDto;
use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Person;
use App\Entity\PersonType;
use App\Entity\Round;
use App\Helper\FileHelper;
use App\Helper\EmailHelper;
use App\Repository\LinkTypeRepository;
use App\Repository\PersonRepository;
use App\Repository\PersonTypeRepository;
use App\Repository\RoundDetailRepository;
use App\Repository\RoundRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

readonly class MediaBusiness
{
    public function __construct(
        private PersonTypeRepository   $personTypeRepository,
        private PersonRepository       $personRepository,
        private LinkTypeRepository     $linkTypeRepository,
        private RoundRepository        $roundRepository,
        private RoundDetailRepository  $roundDetailRepository,
        private FileHelper             $fileHelper,
        private EmailHelper            $emailHelper,
        private SerializerInterface    $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getMedias(
        int $page,
        int $limit,
        ?string $sort = null,
        ?string $order = null,
        ?int $eventId = null,
        ?int $roundId = null,
        ?string $name = null,
        ?string $email = null,
        ?string $phone = null,
        ?bool $selected = null,
        ?bool $selectedMailSent = null,
        ?bool $watchBriefing = null,
        ?bool $generatePass = null
    ): array
    {
        $persons = $this->personRepository->findAllPaginated($sort, $order, $name, $email, $phone, $selected, $selectedMailSent, $watchBriefing, $generatePass, 'media');

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        $mediaPersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personPersonType', 'personType', 'personRoundDetails', 'roundDetail', 'personLinks', 'link', 'linkLinkType', 'linkType']]);

            $medias = $person->getMedias()->filter(function (Media $media) use ($generatePass, $watchBriefing, $selectedMailSent, $selected, $roundId, $eventId) {
                return (!$eventId || $media->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $media->getRound()->getId() === $roundId) &&
                    ($selected === null || $media->isSelected() === $selected) &&
                    ($selectedMailSent === null || $media->isSelectedMailSent() === $selectedMailSent) &&
                    ($watchBriefing === null || $media->isWatchBriefing() === $watchBriefing) &&
                    ($generatePass === null || $media->isGeneratePass() === $generatePass);
            });

            $personArray['medias'] = array_values($medias->toArray());

            if (!empty($personArray['medias'])) {
                $mediaPersons[] = $personArray;
            }
        }

        return [
            'pagination' => [
                'totalItems' => $totalItems,
                'pageIndex' => $page,
                'itemsPerPage' => $limit
            ],
            'medias' => $mediaPersons
        ];
    }

    public function createPersonMedia(MediaDto $mediaDto, UploadedFile $insuranceFile, ?UploadedFile $bookFile): void
    {
        $now = new DateTime();
        $nextRound = $this->roundRepository->findRoundFromDate($now);

        $personType = $this->personTypeRepository->find(1);

        $person = $this->createPerson($mediaDto, $personType, $nextRound);

        if (!empty($mediaDto->instagram)) {
            $this->upsertInstagramLink($person, $mediaDto->instagram);
        }

        $this->createMedia($person, $nextRound, $insuranceFile, $bookFile);

        $this->em->flush();

        $this->emailHelper->sendPreselectedEmail($mediaDto->email, $nextRound, $mediaDto->firstName);
    }

    public function createPerson(MediaDto $mediaDto, PersonType $personType, Round $round): Person
    {
        $person = $this->personRepository->findOneBy(['email' => $mediaDto->email, 'personType' => $personType]);
        if ($person === null) {
            $person = new Person();
            $person->setEmail($mediaDto->email)
                ->setPersonType($personType);
        }

        $person->setFirstName($mediaDto->firstName)
            ->setLastName($mediaDto->lastName)
            ->setPhone($mediaDto->phone)
            ->addRound($round);

        foreach ($mediaDto->presence as $roundDetailId) {
            $roundDetail = $this->roundDetailRepository->find($roundDetailId);
            if ($roundDetail !== null) {
                $person->addRoundDetail($roundDetail);
            }
        }

        $this->em->persist($person);

        return $person;
    }

    public function upsertInstagramLink(Person $person, string $instagram): void
    {
        if ($person->getLinks()->isEmpty() || $person->getLinks()->filter(fn($link) => $link->getLinkType()->getName() === 'Instagram')->isEmpty()) {
            $instaLinkType = $this->linkTypeRepository->findOneBy(['name' => 'Instagram']);

            $instaLink = new Link();
            $instaLink->setLinkType($instaLinkType)
                ->setUrl(ltrim($instagram, '@'))
                ->addPerson($person);
        } else {
            $instaLink = $person->getLinks()->filter(fn($link) => $link->getLinkType()->getName() === 'Instagram')->first();
            $instaLink->setUrl(ltrim($instagram, '@'));
        }

        $this->em->persist($instaLink);
        $this->em->flush();
    }

    public function createMedia(Person $person, Round $round, UploadedFile $insuranceFile, ?UploadedFile $bookFile): Media
    {
        // get round media or create new one
        $media = $person->getMedias()->filter(fn($media) => $media->getRound()?->getId() === $round->getId())->first();
        if ($media === false) {
            $media = new Media();
            $media->setPerson($person)
                ->setRound($round);
        }

        if (!empty($mediaDto->pilotFollow)) {
            $media->setPilotFollow($mediaDto->pilotFollow);
        }

        $path = 'media/' . $round->getId() . '/' . $person->getUniqueId();

        $insuranceFile = $this->fileHelper->saveFile($insuranceFile, $path,  'assurance.' . $insuranceFile->getClientOriginalExtension());
        $media->setInsuranceFilePath($insuranceFile->getPathname());

        if ($bookFile !== null) {
            $bookFile = $this->fileHelper->saveFile($bookFile, $path, 'book' . $bookFile->getClientOriginalExtension());
            $media->setBookFilePath($bookFile->getPathname());
        }

        $this->em->persist($media);

        return $media;
    }

    public function updatePersonMedia(Media $media, MediaDto $mediaDto, ?UploadedFile $insuranceFile, ?UploadedFile $bookFile): void
    {
        // update person
        $person = $media->getPerson();

        $person->setFirstName($mediaDto->firstName)
            ->setLastName($mediaDto->lastName)
            ->setEmail($mediaDto->email)
            ->setPhone($mediaDto->phone)
            ->setWarnings($mediaDto->warnings);

        // Supprimer les détails de rounds qui ne sont plus dans la liste de présence
        foreach ($person->getRoundDetails()->toArray() as $roundDetail) {
            if (!in_array($roundDetail->getId(), $mediaDto->presence)) {
                $person->removeRoundDetail($roundDetail);
            }
        }
        
        // Ajouter les nouveaux détails de rounds
        foreach ($mediaDto->presence as $roundDetailId) {
            // Vérifier si le détail de round existe déjà
            if ($person->getRoundDetails()->exists(fn($key, $rd) => $rd->getId() === $roundDetailId)) {
                continue;
            }

            $roundDetail = $this->roundDetailRepository->find($roundDetailId);
            if ($roundDetail !== null) {
                $person->addRoundDetail($roundDetail);
            }
        }
        

        $this->em->persist($person);

        // update instagram link
        if (!empty($mediaDto->instagram)) {
            $this->upsertInstagramLink($person, $mediaDto->instagram);
        }

        // update media
        $media->setPilotFollow($mediaDto->pilotFollow)
            ->setSelected($mediaDto->selected);

        if ($insuranceFile !== null || $bookFile !== null) {
            $path = 'media/' . $media->getRound()->getId() . '/' . $person->getUniqueId();

            if ($insuranceFile !== null) {
                $insuranceFile = $this->fileHelper->saveFile($insuranceFile, $path, 'assurance.' . $insuranceFile->getClientOriginalExtension());
                $media->setInsuranceFilePath($insuranceFile->getPathname());
            }

            if ($bookFile !== null) {
                $bookFile = $this->fileHelper->saveFile($bookFile, $path, 'book' . $bookFile->getClientOriginalExtension());
                $media->setBookFilePath($bookFile->getPathname());
            }
        }

        $this->em->persist($media);

        $this->em->flush();
    }

    public function updateMediaSelection(Media $media, MediaSelectionDto $mediaSelectionDto): void
    {
        $media->setSelected($mediaSelectionDto->selected);

        $this->em->persist($media);
        $this->em->flush();
    }

    public function deleteMedia(Media $media): void
    {
        $this->em->remove($media);
        $this->em->flush();
    }

    public function deleteMediaBook(Media $media): Media
    {
        $this->fileHelper->deleteFile($media->getBookFilePath());
        $media->setBookFilePath(null);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    public function sendSelectedEmails(Round $round): array
    {
        $errors = [];
        $medias = $round->getMedias()->filter(fn(Media $media) => $media->isSelected() && !$media->isSelectedMailSent());

        foreach ($medias->toArray() as $media) {
            try {
                $this->emailHelper->sendSelectedEmail($media->getPerson()->getEmail(), $round, $media->getPerson()->getFirstName());
                $media->setSelectedMailSent(true);
                $this->em->persist($media);
            } catch (\Exception $e) {
                $errors[] = [
                    'email' => $media->getPerson()?->getEmail(),
                    'error' => $e->getMessage()
                ];
            }
        }

        $this->em->flush();

        return $errors;
    }
}
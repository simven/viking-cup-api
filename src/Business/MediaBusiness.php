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
use Symfony\Component\Serializer\SerializerInterface;

readonly class MediaBusiness
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
        private SerializerInterface $serializer,
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
        ?bool $selected = null,
        ?bool $selectedMailSent = null,
        ?bool $watchBriefing = null,
        ?bool $generatePass = null
    ): array
    {
        $persons = $this->personRepository->findAllPaginated($sort, $order, 'media');

        $adapter = new QueryAdapter($persons, false, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $persons = $pager->getCurrentPageResults();

        $mediaPersons = [];
        /** @var Person $person */
        foreach ($persons as $person) {
            $personArray = $this->serializer->normalize($person, 'json', ['groups' => ['person', 'personPersonType', 'personType', 'personRoundDetails', 'roundDetail']]);

            $medias = $person->getMedias()->filter(function (Media $media) use ($generatePass, $watchBriefing, $selectedMailSent, $selected, $roundId, $eventId) {
                return (!$eventId || $media->getRound()->getEvent()->getId() === $eventId) &&
                    (!$roundId || $media->getRound()->getId() === $roundId) &&
                    (!$selected || $media->isSelected() === $selected) &&
                    (!$selectedMailSent || $media->isSelectedMailSent() === $selectedMailSent) &&
                    (!$watchBriefing || $media->isWatchBriefing() === $watchBriefing) &&
                    (!$generatePass || $media->isGeneratePass() === $generatePass);
            });

            $personArray['medias'] = array_values($medias->toArray());

            $mediaPersons[] = $personArray;
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

        if (!empty($mediaDto->instagram)){
            $this->createInstagramLink($person, $mediaDto->instagram);
        }

        $this->createMedia($person, $nextRound, $insuranceFile, $bookFile);

        $this->em->flush();

        $emailDto = new EmailDto(
            fromName: 'Viking Cup',
            to: $mediaDto->email,
            subject: 'Confirmation de ta demande d\'accréditation média pour le '. $nextRound->getName() . ' de la Viking Cup',
            message: <<<HTML
                <p>Bonjour $mediaDto->firstName,</p>
                <p>Merci pour ta demande d’inscription en tant que média pour la prochaine édition de la <strong>Viking Cup</strong>.</p>
                <p>Ta demande a bien été reçue. Il s’agit pour le moment d’une <strong>demande d’accréditation</strong>, qui sera examinée avec attention par notre équipe.</p>
                <p>Tu recevras une réponse, qu’elle soit positive ou négative, <strong>au plus tard un mois avant le début de la compétition</strong>.</p>
                <p>Tu peux retrouver le règlement média en pièce jointe à ce mail.</p>
                <p>Merci pour l’intérêt que tu portes à la Viking Cup. N’hésite pas à nous contacter si tu as la moindre question.</p>
                <p>Bien cordialement,<br><strong>L’équipe Viking Cup</strong></p>
                HTML,
            attachment: 'https://viking-cup.fr/viking-cup-reglement-media.pdf'
        );
        $this->emailBusiness->sendEmail($emailDto);
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

    public function createInstagramLink(Person $person, string $instagram): void
    {
        if ($person->getLinks()->isEmpty() || $person->getLinks()->filter(fn($link) => $link->getLinkType()->getName() === 'Instagram')->isEmpty()) {
            $instaLinkType = $this->linkTypeRepository->findOneBy(['name' => 'Instagram']);

            $link = new Link();
            $link->setLinkType($instaLinkType)
                ->setUrl(ltrim($instagram, '@'))
                ->addPerson($person);

            $this->em->persist($link);
        }
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

        $file = $this->fileHelper->saveFile($insuranceFile, $path,  'assurance.' . $insuranceFile->getClientOriginalExtension());
        $media->setInsuranceFilePath($file->getPathname());

        if ($bookFile !== null) {
            $bookFile = $this->fileHelper->saveFile($bookFile, $path, 'book' . $bookFile->getClientOriginalExtension());
            $media->setBookFilePath($bookFile->getPathname());
        }

        $this->em->persist($media);

        return $media;
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
}
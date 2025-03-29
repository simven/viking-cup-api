<?php

namespace App\Business;

use App\Dto\QualifDto;
use App\Dto\RoundCategoryPilotsQualifyingDto;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Qualifying;
use App\Entity\Round;
use App\Repository\PilotRoundCategoryRepository;
use App\Repository\QualifyingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class QualifyingBusiness
{
    public function __construct(
        private PilotRoundCategoryRepository $pilotRoundCategoryRepository,
        private QualifyingRepository $qualifyingRepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $em
    )
    {}

    public function getRoundCategoryPilotsQualifying(Round $round, Category $category, ?string $pilot = null): array
    {
        $roundCategoryPilotsQualifying = $this->pilotRoundCategoryRepository->findByRoundCategory($round, $category, $pilot);

        $roundCategoryPilotsQualifyingFormatted = [];
        /** @var PilotRoundCategory $roundCategoryPilot */
        foreach ($roundCategoryPilotsQualifying as $roundCategoryPilot) {
            $roundCategoryPilotQualifyingFormatted = $this->serializer->normalize($roundCategoryPilot, 'json', ['groups' => ['pilotRoundCategory', 'pilotRoundCategoryPilot', 'pilot', 'pilotRoundCategoryQualifyings', 'qualifying']]);

            $pilotEvent = $roundCategoryPilot->getPilot()->getPilotEvents()->filter(fn($pe) => $pe->getEvent()->getId() === $round->getEvent()->getId())->first();
            if ($pilotEvent) {
                $roundCategoryPilotQualifyingFormatted['pilotEvent'] = $this->serializer->normalize($pilotEvent, 'json', ['groups' => ['pilotEvent']]);
            }

            $roundCategoryPilotsQualifyingFormatted[] = $roundCategoryPilotQualifyingFormatted;
        }

        return $roundCategoryPilotsQualifyingFormatted;
    }

    public function updateRoundCategoryPilotsQualifying(array $roundCategoryPilotsQualifyingDto): void
    {
        /** @var RoundCategoryPilotsQualifyingDto $roundCategoryPilotQualif */
        foreach ($roundCategoryPilotsQualifyingDto as $roundCategoryPilotQualif) {
            $pilotRoundCategory = $this->pilotRoundCategoryRepository->find($roundCategoryPilotQualif->id);

            if (!$pilotRoundCategory) {
                continue;
            }

            $pilotRoundCategory->setIsCompeting($roundCategoryPilotQualif->isCompeting);

            $qualifs = $this->serializer->denormalize($roundCategoryPilotQualif->qualifs, QualifDto::class . '[]');
            /** @var QualifDto $qualif */
            foreach ($qualifs as $qualif) {
                if ($qualif->passage !== null) {
                    $qualifying = $this->qualifyingRepository->findOneBy(['pilotRoundCategory' => $pilotRoundCategory, 'passage' => $qualif->passage]);

                    if ($qualif->points === null) {
                        if ($qualifying !== null) {
                            $this->em->remove($qualifying);
                        }
                    } else {
                        if ($qualifying === null) {
                            $qualifying = new Qualifying();
                            $qualifying->setPilotRoundCategory($pilotRoundCategory)
                                ->setPassage($qualif->passage);
                        }
                        $qualifying->setPoints($qualif->points);

                        $this->em->persist($qualifying);
                    }
                }
            }
        }

        $this->em->flush();
    }

    public function getQualifyingRanking(Round $round, Category $category): array
    {
        $pilotRoundCategories = $this->pilotRoundCategoryRepository->findByRoundCategory($round, $category);

        $ranking = [];
        /** @var PilotRoundCategory $pilotRoundCategory */
        foreach ($pilotRoundCategories as $pilotRoundCategory) {
            $firstQualifying = $pilotRoundCategory->getQualifyings()->first();

            if ($firstQualifying === false) {
                continue;
            }

            $maxPilotPoints = $firstQualifying->getPoints();
            foreach ($pilotRoundCategory->getQualifyings() as $qualifying) {
                if ($qualifying->getPoints() > $maxPilotPoints) {
                    $maxPilotPoints = $qualifying->getPoints();
                }
            }

            $ranking[] = [
                'pilotRoundCategory' => $pilotRoundCategory,
                'points' => $maxPilotPoints
            ];
        }

        usort($ranking, fn($a, $b) => $b['points'] <=> $a['points']);

        return $ranking;
    }
}
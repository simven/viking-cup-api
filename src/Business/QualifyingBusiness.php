<?php

namespace App\Business;

use App\Dto\QualifDto;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Qualifying;
use App\Entity\Round;
use App\Helper\RankingHelper;
use App\Repository\PilotRoundCategoryRepository;
use App\Repository\QualifyingRepository;
use App\Repository\RankingPointsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

readonly class QualifyingBusiness
{
    public function __construct(
        private PilotRoundCategoryRepository $pilotRoundCategoryRepository,
        private QualifyingRepository $qualifyingRepository,
        private RankingPointsRepository $rankingPointsRepository,
        private RankingHelper $rankingHelper,
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

    public function updateQualifying(QualifDto $qualifDto): void
    {
        $pilotRoundCategory = $this->pilotRoundCategoryRepository->find($qualifDto->pilotRoundCategoryId);

        if (!$pilotRoundCategory) {
            throw new Exception('Pilot Round Category not found');
        }

        if ($qualifDto->passage !== null) {
            $qualifying = $this->qualifyingRepository->findOneBy(['pilotRoundCategory' => $pilotRoundCategory, 'passage' => $qualifDto->passage]);

            if ($qualifDto->points === null) {
                if ($qualifying !== null) {
                    $this->em->remove($qualifying);
                }
            } else {
                if ($qualifying === null) {
                    $qualifying = new Qualifying();
                    $qualifying->setPilotRoundCategory($pilotRoundCategory)
                        ->setPassage($qualifDto->passage);
                }
                $qualifying->setPoints($qualifDto->points);

                $this->em->persist($qualifying);
                $this->em->flush();
            }
        }
    }

    public function getQualifyingRanking(Round $round, Category $category): array
    {
        $pilotRoundCategories = $this->pilotRoundCategoryRepository->findByRoundCategory($round, $category);
        $rankingPoints = $this->rankingPointsRepository->findBy(['entity' => 'qualifying']);

        $ranking = [];
        /** @var PilotRoundCategory $pilotRoundCategory */
        foreach ($pilotRoundCategories as $pilotRoundCategory) {
            if ($pilotRoundCategory->isCompeting() === false) {
                continue;
            }

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

            $pilotEvent = $pilotRoundCategory->getPilot()->getPilotEvents()->filter(fn($pe) => $pe->getEvent()->getId() === $round->getEvent()->getId())->first();

            $ranking[] = [
                'pilot' => $pilotRoundCategory->getPilot(),
                'pilotEvent' => !$pilotEvent ? null : $pilotEvent,
                'round' => $round,
                'category' => $category,
                'bestPassagePoints' => $maxPilotPoints
            ];
        }

        usort($ranking, fn($a, $b) => $b['bestPassagePoints'] <=> $a['bestPassagePoints']);

        foreach ($ranking as $pos => &$rank) {
            $rank['points'] = $this->rankingHelper->getPointsByPosition($pos + 1, $rankingPoints);
        }

        return $ranking;
    }
}
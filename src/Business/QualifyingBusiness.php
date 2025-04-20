<?php

namespace App\Business;

use App\Dto\QualifDto;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Qualifying;
use App\Entity\Round;
use App\Helper\RankingHelper;
use App\Repository\PilotRoundCategoryRepository;
use App\Repository\QualifyingCriteriaRepository;
use App\Repository\RankingPointsRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class QualifyingBusiness
{
    public function __construct(
        private PilotRoundCategoryRepository $pilotRoundCategoryRepository,
        private RankingPointsRepository $rankingPointsRepository,
        private QualifyingCriteriaRepository $qualifyingCriteriaRepository,
        private RankingHelper $rankingHelper,
        private EntityManagerInterface $em
    )
    {}


    public function getPilotRoundCategoryPilotQualifying(PilotRoundCategory $pilotRoundCategory): array
    {
        return [
            'pilotRoundCategory' => $this->pilotRoundCategoryRepository->findWithCorrectPilotEvent($pilotRoundCategory),
            'pilotQualifyings' => $pilotRoundCategory->getQualifyings()
        ];
    }

    public function updateQualifying(Qualifying $qualifying, QualifDto $qualifDto): void
    {
        $qualifying->setIsValid($qualifDto->isValid);

        $this->em->persist($qualifying);
        $this->em->flush();
    }

    public function getQualifyingRanking(Round $round, Category $category): array
    {
        $pilotRoundCategories = $this->pilotRoundCategoryRepository->findBy(['round' => $round, 'category' => $category]);
        $rankingPoints = $this->rankingPointsRepository->findBy(['entity' => 'qualifying']);
        $criteriaList = $this->qualifyingCriteriaRepository->findBy([], ['priority' => 'ASC']);
        $groupedCriteriaList = $this->groupedCriteriaList($criteriaList);

        $ranking = [];
        /** @var PilotRoundCategory $pilotRoundCategory */
        foreach ($pilotRoundCategories as $pilotRoundCategory) {
            if ($pilotRoundCategory->isEngaged() === false) {
                continue;
            }

            $firstQualifying = $pilotRoundCategory->getQualifyings()->first();

            if ($firstQualifying === false) {
                continue;
            }

            $passagePoints = [];
            $maxPilotPoints = $this->getQualifPassagePoints($firstQualifying);
            foreach ($pilotRoundCategory->getQualifyings() as $qualifying) {
                $qualifyingPoints = $this->getQualifPassagePoints($qualifying);
                $passagePoints[$qualifying->getPassage()] = $qualifyingPoints;
                if ($qualifyingPoints > $maxPilotPoints) {
                    $maxPilotPoints = $qualifyingPoints;
                }
            }

            $pilotEvent = $pilotRoundCategory->getPilot()->getPilotEvents()->filter(fn($pe) => $pe->getEvent()->getId() === $round->getEvent()->getId())->first();

            $ranking[] = [
                'pilot' => $pilotRoundCategory->getPilot(),
                'pilotEvent' => !$pilotEvent ? null : $pilotEvent,
                'round' => $round,
                'category' => $category,
                'passagePoints' => $passagePoints,
                'bestPassagePoints' => $maxPilotPoints,
                'qualifs' => $pilotRoundCategory->getQualifyings()->toArray()
            ];
        }

        usort($ranking, function ($a, $b) use ($groupedCriteriaList) {
            // comparaison meilleurs passages
            if ($b['bestPassagePoints'] !== $a['bestPassagePoints']) {
                return $b['bestPassagePoints'] - $a['bestPassagePoints'];
            }

            // comparaison de la somme des points de passage
            $sumPassagePointsB = array_sum($b['passagePoints']);
            $sumPassagePointsA = array_sum($a['passagePoints']);
            if ($sumPassagePointsB !== $sumPassagePointsA) {
                return $sumPassagePointsB - $sumPassagePointsA;
            }

            // comparaison du passage où le pilote a eu le plus de points
            $bestPassageB = array_search($b['bestPassagePoints'], $b['passagePoints']);
            $bestPassageA = array_search($a['bestPassagePoints'], $a['passagePoints']);
            if ($bestPassageA - $bestPassageB) {
                return $bestPassageA - $bestPassageB;
            }

            // comparaison des points par critère de qualification
            $scoresA = $this->calculateQualifsCriteriaScores($a['qualifs'], $groupedCriteriaList);
            $scoresB = $this->calculateQualifsCriteriaScores($b['qualifs'], $groupedCriteriaList);
            foreach ($scoresA as $priority => $groupA) {
                $groupB = $scoresB[$priority] ?? ['totalPoints' => 0];

                if ($groupB['totalPoints'] !== $groupA['totalPoints']) {
                    return $groupB['totalPoints'] - $groupA['totalPoints'];
                }
            }

            return 0; // égalité parfaite
        });

        foreach ($ranking as $pos => &$rank) {
            $rank['points'] = $this->rankingHelper->getPointsByPosition($pos + 1, $rankingPoints);
            unset($rank['passagePoints']);
            unset($rank['qualifs']);
        }
        
        if ($round->getId() === 1) {
            $ranking = $this->overrideQualifRound1($ranking, $category->getId());
        }

        return $ranking;
    }

    public function groupedCriteriaList(array $criteriaList = []): array
    {
        $groupedScores = [];

        foreach ($criteriaList as $criteria) {
            $priority = $criteria->getPriority();
            $criteriaId = $criteria->getId();

            if (!isset($groupedScores[$priority])) {
                $groupedScores[$priority] = [
                    'criteria' => [],
                    'totalPoints' => 0,
                ];
            }

            $groupedScores[$priority]['criteria'][] = $criteriaId;
        }

        return $groupedScores;
    }

    public function calculateQualifsCriteriaScores(array $qualifs, array $groupedCriteriaList): array
    {
        // Récupération de toutes les qualifications du pilote
        foreach ($qualifs as $qualif) {
            foreach ($qualif->getQualifyingDetails() as $detail) {
                $criteria = $detail->getQualifyingCriteria();
                $priority = $criteria->getPriority();
                $criteriaId = $criteria->getId();
                $points = $detail->getPoints();

                if (isset($groupedCriteriaList[$priority]) && in_array($criteriaId, $groupedCriteriaList[$priority]['criteria'])) {
                    $groupedCriteriaList[$priority]['totalPoints'] += $points;
                }
            }
        }

        // Trie par ordre croissant de priorité
        ksort($groupedCriteriaList);

        return $groupedCriteriaList;
    }

    public function getQualifPassagePoints(Qualifying $qualifying): float
    {
        $points = 0;
        if ($qualifying->isValid() === true) {
            foreach ($qualifying->getQualifyingDetails() as $qualifyingDetail) {
                $points += $qualifyingDetail->getPoints();
            }
        }

        return $points;
    }

    public function overrideQualifRound1(array $qualifRanking, int $categoryId): array
    {
        if ($categoryId === 1) {
            $overrideRanking = [
                ['pilotId' => 13, 'points' => 5, 'pos' => 10],
                ['pilotId' => 15, 'points' => 40, 'pos' => 3],
                ['pilotId' => 24, 'points' => 30, 'pos' => 4],
                ['pilotId' => 33, 'points' => 5, 'pos' => 15],
                ['pilotId' => 37, 'points' => 5, 'pos' => 9],
                ['pilotId' => 10, 'points' => 20, 'pos' => 6],
                ['pilotId' => 50, 'points' => 5, 'pos' => 8],
                ['pilotId' => 53, 'points' => 5, 'pos' => 7],
                ['pilotId' => 9, 'points' => 5, 'pos' => 11],
                ['pilotId' => 57, 'points' => 50, 'pos' => 2],
                ['pilotId' => 60, 'points' => 20, 'pos' => 5],
                ['pilotId' => 61, 'points' => 5, 'pos' => 13],
                ['pilotId' => 62, 'points' => 5, 'pos' => 14],
                ['pilotId' => 63, 'points' => 60, 'pos' => 1],
                ['pilotId' => 54, 'points' => 5, 'pos' => 12],
                ['pilotId' => 19, 'points' => 0, 'pos' => 16],
                ['pilotId' => 20, 'points' => 0, 'pos' => 17],
            ];
        } elseif ($categoryId === 2) {
            $overrideRanking = [
                ['pilotId' => 16, 'points' => 20, 'pos' => 5],
                ['pilotId' => 22, 'points' => 5, 'pos' => 12],
                ['pilotId' => 23, 'points' => 5, 'pos' => 14],
                ['pilotId' => 11, 'points' => 5, 'pos' => 10],
                ['pilotId' => 25, 'points' => 5, 'pos' => 11],
                ['pilotId' => 30, 'points' => 20, 'pos' => 7],
                ['pilotId' => 36, 'points' => 5, 'pos' => 9],
                ['pilotId' => 41, 'points' => 30, 'pos' => 4],
                ['pilotId' => 3, 'points' => 40, 'pos' => 3],
                ['pilotId' => 48, 'points' => 5, 'pos' => 13],
                ['pilotId' => 1, 'points' => 5, 'pos' => 8],
                ['pilotId' => 55, 'points' => 50, 'pos' => 2],
                ['pilotId' => 58, 'points' => 20, 'pos' => 6],
                ['pilotId' => 59, 'points' => 60, 'pos' => 1],
                ['pilotId' => 44, 'points' => 5, 'pos' => 15],
                ['pilotId' => 64, 'points' => 0, 'pos' => 16],
                ['pilotId' => 40, 'points' => 0, 'pos' => 17],
            ];
        }

        if (isset($overrideRanking)) {
            foreach ($qualifRanking as &$ranking) {
                $pilotOverrideIndex = array_search($ranking['pilot']->getId(), array_column($overrideRanking, 'pilotId'));
                if ($pilotOverrideIndex === false) {
                    continue;
                }
                $pilotOverride = $overrideRanking[$pilotOverrideIndex];

                unset($ranking['bestPassagePoints']);
                $ranking['points'] = $pilotOverride['points'];
                $ranking['pos'] = $pilotOverride['pos'];
            }
            unset($ranking); // sécurité PHP foreach

            // Tri par position croissante
            usort($qualifRanking, fn($a, $b) => ($a['pos'] ?? PHP_INT_MAX) <=> ($b['pos'] ?? PHP_INT_MAX));

            foreach ($qualifRanking as &$ranking) {
                unset($ranking['pos']);
            }
            unset($ranking); // sécurité PHP foreach
        }

        return $qualifRanking;
    }

}
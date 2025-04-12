<?php

namespace App\Business;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Round;

readonly class RankingBusiness
{
    public function __construct(
        private QualifyingBusiness $qualifyingBusiness,
        private BattleBusiness $battleBusiness
    )
    {}

    public function getRoundRanking(Round $round, Category $category): array
    {
        $qualifyingRanking = $this->qualifyingBusiness->getQualifyingRanking($round, $category);
        $battleRanking = $this->battleBusiness->getBattleRanking($round, $category);

        // sum of points in qualifying and battles for each pilotRoundCategory
        $pointsByPilotRoundCategory = $this->sumPointsByPilotRoundCategory($qualifyingRanking, $battleRanking);

        // sort by total points
        usort($pointsByPilotRoundCategory, fn($a, $b) => $b['points'] <=> $a['points']);

        return $pointsByPilotRoundCategory;
    }

    public function getEventRanking(Event $event, Category $category): array
    {
        $eventRanking = [];
        foreach ($event->getRounds()->toArray() as $round) {
            $roundRanking = $this->getRoundRanking($round, $category);

            foreach ($roundRanking as $entry) {
                $pilot = $entry['pilot'];

                if (!isset($eventRanking[$pilot->getId()])) {
                    $eventRanking[$pilot->getId()] = [
                        'pilot' => $pilot,
                        'points' => 0,
                    ];
                }

                $eventRanking[$pilot->getId()]['points'] += $entry['points'];
            }
        }

        $eventRanking = array_values($eventRanking);

        // sort by total points
        usort($eventRanking, fn($a, $b) => $b['points'] <=> $a['points']);

        return $eventRanking;
    }

    private function sumPointsByPilotRoundCategory(array $qualifyingRanking, array $battleRanking): array {
        $rankingMerged = array_merge($qualifyingRanking, $battleRanking);

        $grouped = array_reduce($rankingMerged, function ($carry, $entry) {
            $pilotId = $entry['pilot']->getId();
            $points = $entry['points'];

            if (!isset($carry[$pilotId])) {
                $carry[$pilotId] = [
                    'pilot' => $entry['pilot'],
                    'points' => 0
                ];
            }

            $carry[$pilotId]['points'] += $points;

            return $carry;
        }, []);

        return array_values($grouped);
    }
}
<?php

namespace App\Helper;

readonly class RankingHelper
{
    public function getPointsByPosition($position, $rankingPoints): int
    {
        $filtered = array_filter($rankingPoints, fn($rangePoints) =>
            $position >= $rangePoints->getFromPosition() && $position <= $rangePoints->getToPosition()
        );

        return !empty($filtered) ? reset($filtered)->getPoints() : 0;
    }
}
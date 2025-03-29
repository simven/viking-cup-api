<?php

namespace App\Business;

use App\Entity\Battle;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use App\Repository\BattleRepository;
use App\Repository\BattleVersusRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class BattleBusiness
{
    public function __construct(
        private BattleVersusRepository $battleVersusRepository,
        private BattleRepository $battleRepository,
        private QualifyingBusiness $qualifyingBusiness,
        private EntityManagerInterface $em
    )
    {}

    public function getBattleVersus(Round $round, Category $category)
    {
        return $this->battleRepository->getBattleVersus($round, $category);
    }

    public function resetBattle(Round $round, Category $category): void
    {
        $battles = $this->battleRepository->findBy(['round' => $round, 'category' => $category]);

        foreach ($battles as $battle) {
            $this->em->remove($battle);
        }

        $this->em->flush();
    }

    public function initBattleVersus(Round $round, Category $category): void
    {
        $battleVersus = $this->battleVersusRepository->findAll();
        $qualifyingRanking = $this->qualifyingBusiness->getQualifyingRanking($round, $category);

        foreach ($battleVersus as $versus) {
            $pilotRoundCategory1 = $qualifyingRanking[$versus->getPilotQualifPosition1() -1]['pilotRoundCategory'] ?? null;
            if ($pilotRoundCategory1?->isCompeting() === false) {
                $pilotRoundCategory1 = null;
            }
            $pilotRoundCategory2 = $qualifyingRanking[$versus->getPilotQualifPosition2() -1]['pilotRoundCategory'] ?? null;
            if ($pilotRoundCategory2?->isCompeting() === false) {
                $pilotRoundCategory2 = null;
            }

            if ($pilotRoundCategory1 !== null && $pilotRoundCategory2 !== null) {
                if ($pilotRoundCategory1->getSecondPilot()?->getId() === $pilotRoundCategory2->getPilot()->getId() ||
                    $pilotRoundCategory2->getSecondPilot()?->getId() === $pilotRoundCategory1->getPilot()->getId()) {

                    $battle = new Battle();
                    $battle->setPilotRoundCategory1($pilotRoundCategory1)
                        ->setPilotRoundCategory2($pilotRoundCategory2)
                        ->setWinner($pilotRoundCategory1->isMainPilot() ? $pilotRoundCategory1 : $pilotRoundCategory2)
                        ->setPassage(1);
                    $this->em->persist($battle);

                    continue;
                }
            }

            $battle = new Battle();
            $battle->setPilotRoundCategory1($pilotRoundCategory1)
                ->setPilotRoundCategory2($pilotRoundCategory2)
                ->setPassage(1);

            // if one of versus is null, so the other is the winner
            if ($pilotRoundCategory1 === null || $pilotRoundCategory2 === null) {
                $winner = $pilotRoundCategory1 ?? $pilotRoundCategory2;
                $battle->setWinner($winner);
            }
            $this->em->persist($battle);
        }

        $this->em->flush();
    }

    public function generateNextRound(Round $round, Category $category, int $passage): void
    {
        $nextPassage = $passage + 1;
        $nextBattles = $this->battleRepository->getBattleVersus($round, $category, $nextPassage);

        if (count($nextBattles) > 0) {
            throw new \Exception("Le tour suivant a déjà été généré !");
        }

        $battles = $this->battleRepository->getBattleVersus($round, $category, $passage);
        $winners = array_filter($battles, fn(Battle $battle) => $battle->getWinner() !== null);

        if (count($battles) !== count($winners)) {
            throw new \Exception("Impossible de générer le tour suivant, il manque des gagnants !");
        }

        if (count($winners) === 1) {
            return;
        }

        if ($nextPassage === 5) {
            $this->generateThirdPlacePlayoff($winners, $nextPassage + 1);
        }

        for ($i = 0; $i < count($winners); $i += 2) {
            $winner1 = isset($winners[$i]) ? $winners[$i]->getWinner() : null;
            if ($winner1->isCompeting() === false) {
                $winner1 = null;
            }
            $winner2 = isset($winners[$i + 1]) ? $winners[$i + 1]->getWinner() : null;
            if ($winner2->isCompeting() === false) {
                $winner2 = null;
            }

            $battle = new Battle();
            $battle->setPilotRoundCategory1($winner1)
                ->setPilotRoundCategory2($winner2)
                ->setPassage($nextPassage);

            if ($winner1 === null || $winner2 === null) {
                $winner = $winner1 ?? $winner2;
                $battle->setWinner($winner);
            }

            $this->em->persist($battle);
        }

        $this->em->flush();
    }

    public function setBattleWinner(Battle $battle, PilotRoundCategory $winner): void
    {
        if ($battle->getWinner() !== null) {
            $nextPassage = $battle->getPassage() + 1;
            $nextBattles = $this->battleRepository->getBattleVersus($winner->getRound(), $winner->getCategory(), $nextPassage);

            foreach ($nextBattles as $nextBattle) {
                if ($battle->getWinner() === $nextBattle->getPilotRoundCategory1()) {
                    $nextBattle->setPilotRoundCategory1($winner);
                    $this->em->persist($nextBattle);
                } elseif ($battle->getWinner() === $nextBattle->getPilotRoundCategory2()) {
                    $nextBattle->setPilotRoundCategory2($winner);
                    $this->em->persist($nextBattle);
                }
            }
        }

        $battle->setWinner($winner);
        $this->em->persist($battle);
        $this->em->flush();
    }

    public function getBattleRanking(Round $round, Category $category): array
    {
        $battleRanking = $this->battleRepository->getBattleRanking($round, $category);

        return array_map(fn($battle) => [
            'pilotRoundCategory' => $battle[0] ?? null,
            'wins' => (int)$battle['wins'],
            'loses' => (int)$battle['loses'],
            'last_defeat_passage' => (int)$battle['last_defeat_passage']
        ], $battleRanking);
    }

    private function generateThirdPlacePlayoff(array $winners, int $passage): void
    {
        $losers = array_map(fn(Battle $battle) => $battle->getWinner() === $battle->getPilotRoundCategory1() ? $battle->getPilotRoundCategory2() : $battle->getPilotRoundCategory1(), $winners);

        if (count($losers) !== 2) {
            return;
        }

        $battle = new Battle();
        $battle->setPilotRoundCategory1($losers[0])
            ->setPilotRoundCategory2($losers[1])
            ->setPassage($passage);

        $this->em->persist($battle);
    }
}
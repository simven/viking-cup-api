<?php

namespace App\Business;

use App\Dto\PilotRoundCategoryDto;
use App\Entity\PilotRoundCategory;
use Doctrine\ORM\EntityManagerInterface;

readonly class PilotRoundCategoryBusiness
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}

    public function update(PilotRoundCategory $pilotRoundCategory, PilotRoundCategoryDto $pilotRoundCategoryDto): void
    {
        $pilotRoundCategory->setIsCompeting($pilotRoundCategoryDto->isCompeting);

        $this->em->persist($pilotRoundCategory);
        $this->em->flush();
    }
}
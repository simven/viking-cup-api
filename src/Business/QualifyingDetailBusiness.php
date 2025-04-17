<?php

namespace App\Business;

use App\Dto\QualifDetailDto;
use App\Entity\Qualifying;
use App\Entity\QualifyingDetail;
use App\Repository\QualifyingCriteriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class QualifyingDetailBusiness
{
    public function __construct(
        private QualifyingCriteriaRepository $qualifyingCriteriaRepository,
        private EntityManagerInterface $em
    )
    {}

    public function updateQualifyingDetail(Qualifying $qualifying, QualifDetailDto $qualifDetailDto): void
    {
        $qualifyingDetail = $qualifying->getQualifyingDetails()->filter(fn($qualifDetail) => $qualifDetail->getQualifyingCriteria()->getId() === $qualifDetailDto->qualifyingCriteriaId)->first();

        if ($qualifyingDetail === false) {
            $qualifyingCriteria = $this->qualifyingCriteriaRepository->find($qualifDetailDto->qualifyingCriteriaId);
            if ($qualifyingCriteria === null) {
                throw new Exception('Qualifying criteria not found');
            }

            $qualifyingDetail = new QualifyingDetail();
            $qualifyingDetail->setQualifying($qualifying)
                ->setQualifyingCriteria($qualifyingCriteria);
        }

        $qualifyingDetail->setPoints($qualifDetailDto->points)
            ->setComment($qualifDetailDto->comment);

        $this->em->persist($qualifyingDetail);
        $this->em->flush();
    }
}
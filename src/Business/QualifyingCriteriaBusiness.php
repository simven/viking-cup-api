<?php

namespace App\Business;

use App\Repository\QualifyingCriteriaRepository;

readonly class QualifyingCriteriaBusiness
{
    public function __construct(
        private QualifyingCriteriaRepository $qualifyingCriteriaRepository
    )
    {}

    public function getQualifyingCriteria(): array
    {
        return $this->qualifyingCriteriaRepository->findAll();
    }
}
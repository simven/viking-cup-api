<?php

namespace App\Business;

use App\Dto\PilotRoundCategoryDto;
use App\Entity\Category;
use App\Entity\PilotRoundCategory;
use App\Entity\Round;
use App\Repository\PilotRoundCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

readonly class PilotRoundCategoryBusiness
{
    public function __construct(
        private PilotRoundCategoryRepository $pilotRoundCategoryRepository,
        private EntityManagerInterface $em
    )
    {}

    public function getPilotRoundCategoryPaginate(
        Round $round,
        Category $category,
        int $page,
        int $limit,
        ?string $sort,
        ?string $order,
        ?string $search = null
    ): array
    {
        $query = $this->pilotRoundCategoryRepository->findByRoundCategoryQuery(
            $round,
            $category,
            $sort,
            $order,
            $search
        );

        $adapter = new QueryAdapter($query, false);
        $pager = new Pagerfanta($adapter);
        $totalItems = $pager->count();
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);
        $pilotRoundCategories = $pager->getCurrentPageResults();

        return [
            'pilotRoundCategories' => $pilotRoundCategories,
            'pagination' => [
                'totalItems' => $totalItems,
                'currentPage' => $page,
                'itemsPerPage' => $limit,
            ],
        ];
    }

    public function update(PilotRoundCategory $pilotRoundCategory, PilotRoundCategoryDto $pilotRoundCategoryDto): void
    {
        $pilotRoundCategory->setIsCompeting($pilotRoundCategoryDto->isCompeting);
        $pilotRoundCategory->setIsEngaged($pilotRoundCategoryDto->isEngaged);

        $this->em->persist($pilotRoundCategory);
        $this->em->flush();
    }
}
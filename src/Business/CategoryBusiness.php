<?php

namespace App\Business;

use App\Repository\CategoryRepository;

readonly class CategoryBusiness
{
    public function __construct(
        private CategoryRepository $categoryRepository
    )
    {}

    public function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}
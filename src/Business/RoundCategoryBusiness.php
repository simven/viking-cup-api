<?php

namespace App\Business;

use App\Dto\RoundCategoryDto;
use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Round;
use App\Entity\RoundCategory;
use Doctrine\ORM\EntityManagerInterface;

readonly class RoundCategoryBusiness
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}

    public function getRoundCategory(Round $round, Category $category): ?RoundCategory
    {
        $roundCategory = $round->getRoundCategories()->filter(
            fn($roundCategory) => $roundCategory->getCategory()->getId() === $category->getId()
        )->first();

        if ($roundCategory === false) {
            return null;
        }

        return $roundCategory;
    }

    public function displayTop(Category $category, ?Round $round = null, ?Event $event = null): bool
    {
        if ($round !== null) {
            $roundCategory = $round->getRoundCategories()->filter(
                fn($roundCategory) => $roundCategory->getCategory()->getId() === $category->getId()
            )->first();

            if ($roundCategory === false) {
                return true;
            }

            return $roundCategory->isDisplayTop();
        } else {
            foreach ($category->getRoundCategories() as $roundCategory) {
                if ($roundCategory->getRound()->getEvent()->getId() === $event->getId() && $roundCategory->isDisplayTop() === false) {
                    return false;
                }
            }

            return true;
        }
    }

    public function updateRoundCategory(RoundCategory $roundCategory, RoundCategoryDto $roundCategoryDto): RoundCategory
    {
        $roundCategory->setDisplayTop($roundCategoryDto->displayTop);

        $this->em->persist($roundCategory);
        $this->em->flush();

        return $roundCategory;
    }
}
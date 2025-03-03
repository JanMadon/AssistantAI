<?php

namespace App\DesignPatterns\Creational\FactoryMethod;

final class MealFactory
{
    public function create(MealType $mealType): MealInterface
    {
        return match ($mealType) {
            MealType::VEGETARIAN => new VegetarianMeal(),
            MealType::VEGAN => new VeganMeal()
        };
    }
}
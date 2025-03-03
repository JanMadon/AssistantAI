<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\FactoryMethod;

final class VeganMeal implements MealInterface
{
    public function containsAnimalProducts(): false
    {
        return false;
    }
}
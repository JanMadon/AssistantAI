<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\SimpleFactory;

final class VeganMeal implements MealInterface
{
    public function containsAnimalProducts(): false
    {
        return false;
    }
}
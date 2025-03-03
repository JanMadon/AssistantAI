<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\FactoryMethod;

interface MealInterface
{
    public function containsAnimalProducts(): bool;
}
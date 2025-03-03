<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\SimpleFactory;

interface MealInterface
{
    public function containsAnimalProducts(): bool;
}
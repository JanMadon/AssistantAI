<?php

namespace App\DesignPatterns\Creational\FactoryMethod;

interface MealFactoryInterface
{
    public function createMeal(): MealInterface;
}
<?php

namespace App\DesignPatterns\Creational\FactoryMethod\Test;


use App\DesignPatterns\Creational\FactoryMethod\MealInterface;
use App\DesignPatterns\Creational\FactoryMethod\VegetarianMeal;
use App\DesignPatterns\Creational\FactoryMethod\VegetarianMealFactory;
use PHPUnit\Framework\TestCase;

final class VegetarianMealFactoryTest extends TestCase
{
    public function testCanCreateVegetarianMeal(): void
    {
        $meal = (new VegetarianMealFactory())->createMeal();

        self::assertInstanceOf(MealInterface::class, $meal);
        self::assertInstanceOf(VegetarianMeal::class, $meal);
    }
}
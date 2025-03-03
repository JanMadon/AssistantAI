<?php

namespace App\DesignPatterns\Creational\SimpleFactory\Test;

use App\DesignPatterns\Creational\SimpleFactory\MealFactory;
use App\DesignPatterns\Creational\SimpleFactory\MealInterface;
use App\DesignPatterns\Creational\SimpleFactory\MealType;
use App\DesignPatterns\Creational\SimpleFactory\VeganMeal;
use App\DesignPatterns\Creational\SimpleFactory\VegetarianMeal;
use PHPUnit\Framework\TestCase;

final class MealFactoryTest extends TestCase
{
    public function testCanCreateVegetarianMeal(): void
    {

        $meal = (new MealFactory())->create(MealType::VEGETARIAN);

        self::assertInstanceOf(MealInterface::class, $meal);
        self::assertInstanceOf(VegetarianMeal::class, $meal);
    }

    public function testCanCreateVeganMeal(): void
    {
        $meal = (new MealFactory())->create(MealType::VEGAN);

        self::assertInstanceOf(MealInterface::class, $meal);
        self::assertInstanceOf(VeganMeal::class, $meal);
    }
}
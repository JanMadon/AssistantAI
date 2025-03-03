<?php

namespace App\DesignPatterns\Creational\AbstractFactory;

final class VegetarianMealFactory extends AbstractMealFactory
{
    public function createBreakfast(): BreakfastInterface
    {
        return new VegetarianBreakfast();
    }

    public function createDinner(): DinnerInterface
    {
        return new VegetarianDinner();
    }
}
<?php

namespace App\DesignPatterns\Creational\AbstractFactory;

abstract class AbstractMealFactory
{
    abstract public function createBreakfast(): BreakfastInterface;

    abstract public function createDinner(): DinnerInterface;
}
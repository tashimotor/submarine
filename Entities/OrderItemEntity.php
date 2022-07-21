<?php

namespace Entities;

/**
 * Сущность описывающая позицию заказа.
 */
class OrderItemEntity
{
    /**
     * @var string Название
     */
    protected string $name;

    /**
     * @param  string  $name  Название
     * @param  float  $price  Цена
     */
    public function __construct(string $name, protected float $price)
    {
        $this->name = trim($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }
}
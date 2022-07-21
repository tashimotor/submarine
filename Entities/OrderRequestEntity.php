<?php

namespace Entities;

/**
 * Сущность описывающая данные для заказа полученные через request
 */
class OrderRequestEntity
{
    /**
     * @var array<OrderItemEntity> Позиции заказа
     */
    protected array $items;

    /**
     * @param  int  $userId  ID пользователя
     * @param  array<int, string>  $items  Названия позиций заказа
     * @param  array<int, float>  $prices  Цены позиций заказа
     */
    public function __construct(
        protected int $userId,
        array $items,
        array $prices
    ) {
        foreach ($items as $key => $item) {
            $this->items[$key] = new OrderItemEntity(
                $item,
                $prices[$key]
            );
        }
    }

    /**
     * @return  array<OrderItemEntity>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

}
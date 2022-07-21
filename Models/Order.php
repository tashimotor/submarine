<?php

namespace Models;


use Modules\Database\MysqlLink;
use RuntimeException;

/**
 * Модель заказа
 */
class Order
{
    /**
     * @var float Сумма заказа
     */
    protected float $sum = 0.0;

    /**
     * @var array<string> Список названий позиций заказа
     */
    protected array $itemNames;

    /**
     * @param  MysqlLink  $mysql  Коннектор к БД
     * @param  int  $userId  ID пользователя
     */
    public function __construct(protected MysqlLink $mysql, protected int $userId)
    {
        // Nothing
    }

    /**
     * Добавление позиции к заказу
     *
     * @param  string  $name
     * @param  float  $price
     *
     * @return void
     */
    public function addItem(string $name, float $price): void
    {
        $this->itemNames[] = $name;
        $this->sum         += $price;
    }

    /**
     * Получить сумму заказа
     *
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * Сохранить заказ
     *
     * @return int ID заказа
     */
    public function save(): int
    {
        $query = sprintf(
            "INSERT INTO orders ('user_id', 'items', 'sum') VALUES (%d, '%s', %d)",
            $this->userId,
            $this->getItemNames(),
            $this->sum
        );

        if (!mysqli_query($this->mysql->getLink(), $query)) {
            throw  new RuntimeException('Ошибка создание заказа.');
        }

        return mysqli_insert_id($this->mysql->getLink());
    }

    /**
     * Получить названия позиций заказа
     *
     * @return string
     */
    protected function getItemNames(): string
    {
        return mysqli_real_escape_string($this->mysql->getLink(), implode(';', $this->itemNames));
    }
}
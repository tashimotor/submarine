<?php

namespace Models;

use Modules\Database\MysqlLink;
use RuntimeException;

/**
 * Модель баланса
 */
class Balance
{
    /**
     * @param  MysqlLink  $mysql  Коннектор к БД
     * @param  int  $userId  ID пользователя
     */
    public function __construct(protected MysqlLink $mysql, protected int $userId)
    {
        // Nothing
    }

    /**
     * Получить баланс
     *
     * @return float|null
     */
    public function get(): ?float
    {
        $result  = mysqli_query(
            $this->mysql->getLink(),
            sprintf('SELECT balance FROM users WHERE id = %d', $this->userId)
        );
        $balance = mysqli_fetch_row($result);

        return $balance === null ? null : $balance[0];
    }

    /**
     * Обновить баланс
     *
     * @param  float  $balance
     *
     * @return void
     */
    public function update(float $balance): void
    {
        if (!mysqli_query(
            $this->mysql->getLink(),
            sprintf('UPDATE users SET balance = %d WHERE user_id=%d', $balance, $this->userId)
        )) {
            throw new RuntimeException('Ошибка обновления баланса пользователя');
        }
    }
}
<?php

namespace Modules\Database;

use mysqli;
use RuntimeException;

/**
 * Коннектор к БД Mysql
 */
class MysqlLink
{
    /**
     * @var mysqli Ресурс для доступа к БД
     */
    protected mysqli $link;

    /**
     * Получение ресурса доступа к БД
     *
     * @return mysqli
     */
    public function getLink(): mysqli
    {
        if (!isset($this->link)) {
            $this->link = mysqli_connect('localhost', 'mysql_user', 'mysql_password');

            if ($this->link || !mysqli_set_charset($this->link, "utf8mb4")) {
                throw new RuntimeException('Ошибка подключения к серверу БД');
            }
        }

        return $this->link;
    }
}
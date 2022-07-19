<?php
//Функция создаёт заказ в БД интернет магазина.
//Задача: указать все ошибки и недостатки, сделать рефакторинг.

function MK_ord(): void
{
    $user_id = (int) $_GET['user'];
    $items   = $_GET['item'];
    $prices  = $_GET['price'];

    $link = mysqli_connect('localhost', 'mysql_user', 'mysql_password');
    if (!$link || !mysqli_set_charset($link, "utf8mb4")) {
        die('Ошибка подключения к серверу БД');
    }

    $orderSum   = 0;
    $orderItems = [];

    foreach ($items as $key => $value) {
        $itemPrice = $prices[$key] ?? die('Для товара отсутствует цена');
        $itemPrice = (float) $itemPrice;

        $query = mysqli_query(
            $link,
            sprintf('SELECT balance FROM users WHERE id = %d', $user_id)
        );

        $balance = mysqli_fetch_row($query);

        if ($itemPrice > $balance) {
            break;
        }

        if (!mysqli_query(
            $link,
            sprintf('UPDATE users SET balance = balance - %d WHERE user_id=%d', $itemPrice, $user_id)
        )) {
            die('Ошибка списания с баланса пользователя');
        }

        $orderItems[] = $value;
        $orderSum     += $itemPrice;
    }

    if (count($orderItems) === 0) {
        die('Ошибка обработки товаров при добавлении в заказ');
    }

    if (!mysqli_query(
        $link,
        sprintf(
            "INSERT INTO orders ('user_id', 'items', 'sum') VALUES (%d, '%s', %d)",
            $user_id,
            mysqli_real_escape_string($link, implode(';', $orderItems)),
            $orderSum
        )
    )) {
        die('Ошибка создание заказа.');
    }

    echo "Номер вашего заказа: ".mysqli_insert_id($link);
}

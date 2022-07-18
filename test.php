<?php
//Функция создаёт заказ в БД интернет магазина.
//Задача: указать все ошибки и недостатки, сделать рефакторинг.

function MK_ord()
{
    $user_id = $_GET['user'];
    $items   = $_GET['item'];
    $prices  = $_GET['price'];

    $link = mysqli_connect('localhost', 'mysql_user', 'mysql_password');
    if (!$link) {
        die('Ошибка');
    }

    foreach ($items as $key => $value) {
        $query   = mysqli_query($link, "SELECT balance FROM users WHERE id = '$user_id'");
        $balance = mysqli_fetch_row($query);

        if ($prices[$key] < $balance) {
            $sql = "UPDATE users SET balance = balance - ".$prices[$key];
            if (!mysqli_query($link, $sql)) {
                die('Ошибка');
            }
            $order_items[] = $value;
            $order_price   += $prices[$key];
        }
    }

    $sql = "INSERT INTO orders ($user_id, $items, $sum) 
			VALUES ($user_id, '".implode(';', $order_items)."', $order_price)";

    if (!mysqli_query($link, $sql)) {
        die('Ошибка');
    }

    echo "Номер вашего заказа: ".mysqli_insert_id($link);
}

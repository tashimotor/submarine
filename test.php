<?php

use Entities\OrderRequestEntity;
use Models\Balance;
use Models\Order;
use Modules\Database\MysqlLink;

/**
 * Функция создаёт заказ в БД интернет магазина.
 * Задача: указать все ошибки и недостатки, сделать рефакторинг.
 *
 * @return void
 */
function MK_ord(): void
{
    $orderRequest = new OrderRequestEntity($_GET['user'], $_GET['item'], $_GET['price']);

    $database = new MysqlLink();

    $balance    = new Balance($database, $orderRequest->getUserId());
    $balanceSum = $balance->get();
    if ($balanceSum === null) {
        die('Ошибка получения баланса пользователя');
    }

    $order = new Order($database, $orderRequest->getUserId());

    foreach ($orderRequest->getItems() as $item) {
        $order->addItem($item->getName(), $item->getPrice());

        if ($order->getSum() < $balanceSum) {
            die('Недостаточно средств');
        }
    }

    mysqli_begin_transaction($database->getLink());

    try {
        $balance->update($balanceSum - $order->getSum());
        $orderId = $order->save();

        mysqli_commit($database->getLink());

        echo "Номер вашего заказа: ".$orderId;
    } catch (Throwable $e) {
        mysqli_rollback($database->getLink());

        die ($e->getMessage());
    }
}

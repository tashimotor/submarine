<?php
//Функция создаёт заказ в БД интернет магазина.
//Задача: указать все ошибки и недостатки, сделать рефакторинг.

/**
 * Самая серьезная проблема - может быть нарушена консистентность данных. Баланс может уменьшиться,
 * а заказ не создаться. Для того чтобы это не происходило, логику надо завернуть в транзакцию.
 * Добавить обработку исключений, и при получении исключения откатывать транзакцию.
 *
 * Вторая проблема - запросы в цикле. Это снижает производительность.
 * Тут можно предложить не запрашивать и не списывать каждый раз баланс. Запросить один раз и списывать в памяти.
 * Я не стал этого делать, потому что не знаю контекста работы функции.
 * Непонятно должен ли заказ частично создаваться, или на балансе должны быть полная сумма всех товаров.
 *
 * Третья проблема - подстановка в запросы данных сразу из глобальных переменных, тут может получиться sql-инъекция.
 * Я добавил обработку данных(экранирование и приведение к типу).
 *
 * Четвертая проблема - не проверяются данные полученные от клиента. Возможны кейсы когда в массиве prices, нет цены для
 * какого-то товара. Я добавил примитивную проверку, но это необходимо делать отдельно и в другом места. Это не зона
 * ответственности этой функции.
 *
 * Остальное по мелочи будет понятно из кода.
 *
 * @return void
 *
 */
function MK_ord(): void
{
    $userId = (int) $_GET['user'];
    $items  = $_GET['item'];
    $prices = $_GET['price'];

    if (empty($userId) || empty($items) || empty($prices)) {
        die('Ошибка полученных данных');
    }

    $link = mysqli_connect('localhost', 'mysql_user', 'mysql_password');
    if (!$link || !mysqli_set_charset($link, "utf8mb4")) {
        die('Ошибка подключения к серверу БД');
    }

    $orderSum   = 0.0;
    $orderItems = [];

    try {
        mysqli_begin_transaction($link);

        foreach ($items as $key => $value) {
            $itemPrice = (float) ($prices[$key] ?? throw new RuntimeException('Для товара отсутствует цена'));

            $query = mysqli_query(
                $link,
                sprintf('SELECT balance FROM users WHERE id = %d', $userId)
            );

            $balance = mysqli_fetch_row($query);

            if ($balance === false || $balance === null) {
                throw new RuntimeException('Ошибка получения баланса пользователя');
            }

            if ($itemPrice > $balance[0]) {
                break;
            }

            if (!mysqli_query(
                $link,
                sprintf('UPDATE users SET balance = balance - %d WHERE user_id=%d', $itemPrice, $userId)
            )) {
                throw new RuntimeException('Ошибка списания с баланса пользователя');
            }

            $orderItems[] = $value;
            $orderSum     += $itemPrice;
        }

        if (count($orderItems) === 0) {
            throw new RuntimeException('Ошибка обработки товаров при добавлении в заказ');
        }

        if (!mysqli_query(
            $link,
            sprintf(
                "INSERT INTO orders ('user_id', 'items', 'sum') VALUES (%d, '%s', %d)",
                $userId,
                mysqli_real_escape_string($link, implode(';', $orderItems)),
                $orderSum
            )
        )) {
            throw new RuntimeException('Ошибка создание заказа.');
        }

        mysqli_commit($link);
        echo "Номер вашего заказа: ".mysqli_insert_id($link);
    } catch (Throwable $e) {
        mysqli_rollback($link);
        die ($e->getMessage());
    }
}

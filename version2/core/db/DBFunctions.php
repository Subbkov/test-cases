<?php

declare(strict_types=1);

/**
 * Получает данные о пользователях у которых заканчивается подписка
 *
 * @param mysqli $mysqli
 * @param array $daysSubscriptionExpiring
 *
 * @return mysqli_result
 *
 * @throws Exception
 */
function getSubscriptionExpiringData(mysqli $mysqli, array $daysSubscriptionExpiring): mysqli_result
{
    $sql = 'SELECT 
                username,
                email
            FROM 
                 user 
            WHERE 
                valid = ?
                    AND
                {validtsTemplate}
            ORDER BY validts DESC';

    [$validtsSql, $validtsParams] = prepareValidtsSubscriptionExpiring($daysSubscriptionExpiring);

    $sql = str_replace('{validtsTemplate}', $validtsSql, $sql);

    $result = selectDb($mysqli, $sql, array_merge([true], $validtsParams));

    if (!$result instanceof mysqli_result) {
        throw new Exception('[mysqli] Ошибка получения данных окончания подписки');
    }

    return $result;
}

/**
 * Возвращает часть SQL условия для validts + параметры для validts
 *
 * @param array $daysSubscriptionExpiring - массив дней (за сколько делать выборку)
 *
 * @return array
 */
function prepareValidtsSubscriptionExpiring(array $daysSubscriptionExpiring): array
{
    $timestamp = time();
    $sql = '(';
    $params = [];
    $lastKey = array_key_last($daysSubscriptionExpiring);

    foreach ($daysSubscriptionExpiring as $validtsKey => $dayStart) {
        $dayEnd = $dayStart + 1;

        [$timestampStart, $timestampEnd] = buildTimestampsDb($timestamp, $dayStart, $dayEnd);

        $sql .= 'validts BETWEEN ? AND ? ';

        if ($lastKey !== $validtsKey) {
            $sql .= ' OR ';
        }

        $params[] = $timestampStart;
        $params[] = $timestampEnd;
    }

    $sql .= ')';

    return [$sql, $params];
}

/**
 * Возвращает данные о timestamp старта и окончания выборки
 *
 * @param int $timestamp
 * @param int $multiplierStart
 * @param int $multiplierEnd
 *
 * @return int[]
 */
function buildTimestampsDb(int $timestamp, int $multiplierStart, int $multiplierEnd): array
{
    return [
        $timestamp + ($multiplierStart * DAY_SECONDS),
        $timestamp + ($multiplierEnd * DAY_SECONDS),
    ];
}

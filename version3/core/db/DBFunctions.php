<?php

declare(strict_types=1);

/**
 * Получает данные о пользователях у которых заканчивается подписка
 *
 * @param mysqli $mysqli
 * @param array $daysSubscriptionExpiring
 * @param int $currentTimestamp
 * @param int $limit
 *
 * @return mysqli_result
 *
 * @throws Exception
 */
function getSubscriptionExpiringData(
    mysqli $mysqli,
    array $daysSubscriptionExpiring,
    int $currentTimestamp,
    int $limit = 100
): mysqli_result
{
    $sql = 'SELECT 
                id,
                username,
                email
            FROM 
                 user 
            WHERE 
                valid = ?
                    AND
                {validtsTemplate}
                    AND 
                (last_sent IS NULL OR last_sent > ?)
            ORDER BY validts DESC
            LIMIT ?';

    [$validtsSql, $validtsParams] = prepareValidtsSubscriptionExpiring($daysSubscriptionExpiring, $currentTimestamp);

    $sql = str_replace('{validtsTemplate}', $validtsSql, $sql);

    $lastSent = $currentTimestamp + DAY_SECONDS;

    $params = array_merge(
        [true],
        $validtsParams,
        [$lastSent],
        [$limit]
    );

    $result = selectDb($mysqli, $sql, $params);

    if (!$result instanceof mysqli_result) {
        throw new Exception('[mysqli] Ошибка получения данных окончания подписки');
    }

    return $result;
}

/**
 * Обновляется timestamp последней отправки почты
 *
 * @param mysqli $mysqli
 * @param int $id
 * @param int $timestamp
 *
 * @return void
 *
 * @throws Exception
 */
function updateLastSent(mysqli $mysqli, int $id, int $timestamp): void
{
    $affectedRows = updateDb(
        $mysqli,
        'UPDATE user SET last_sent=? WHERE id = ?',
        [$timestamp, $id]
    );

    if (0 === $affectedRows) {
        throw new Exception('[mysqli] Ошибка обновления данных. user.last_sent для id ' . $id . ' не обновлён.');
    }
}

/**
 * Возвращает часть SQL условия для validts + параметры для validts
 *
 * @param array $daysSubscriptionExpiring - массив дней (за сколько делать выборку)
 * @param int $currentTimestamp
 *
 * @return array
 */
function prepareValidtsSubscriptionExpiring(array $daysSubscriptionExpiring, int $currentTimestamp): array
{
    $sql = '(';
    $params = [];
    $lastKey = array_key_last($daysSubscriptionExpiring);

    foreach ($daysSubscriptionExpiring as $validtsKey => $dayStart) {
        $dayEnd = $dayStart + 1;

        [$timestampStart, $timestampEnd] = buildTimestampsDb($currentTimestamp, $dayStart, $dayEnd);

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

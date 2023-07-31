<?php

declare(strict_types=1);


/**
 * Получает данные о пользователях у которых заканчивается подписка из очереди
 *
 * @param mysqli $mysqli
 * @param int $groupId
 * @param int $limit
 *
 * @return mysqli_result
 *
 * @throws Exception
 */
function getQueueSubscriptionExpiringData(mysqli $mysqli, int $groupId, int $limit = 100): mysqli_result
{
    $sql = 'SELECT 
                u.username,
                u.email,
                qu.id AS qu_id
            FROM 
                 user u
			INNER JOIN
				queue_user_subscription_expiration qu
                ON qu.user_id = u.id
            WHERE
                qu.group_id = ?
            ORDER BY qu_id ASC
            LIMIT ?';

    $result = selectDb($mysqli, $sql, [$groupId, $limit]);

    if (!$result instanceof mysqli_result) {
        throw new Exception('[mysqli] Ошибка получения данных из очереди для группы ' . $groupId);
    }

    return $result;
}

/**
 * Удаляет данные из очереди
 *
 * @param mysqli $mysqli
 * @param int $id
 *
 * @return void
 *
 * @throws Exception
 */
function deleteQueueId(mysqli $mysqli, int $id): void
{
    $sql = 'DELETE FROM queue_user_subscription_expiration WHERE id = ?';

    $affectedRows = deleteDb($mysqli, $sql, [$id]);

    if (0 === $affectedRows) {
        throw new Exception('[mysqli] Ошибка Удаления данных из очереди. Для id ' . $id);
    }
}

/**
 * Получает данные о пользователях у которых заканчивается подписка
 *
 * @param mysqli $mysqli
 * @param array $daysSubscriptionExpiring
 * @param int $limit
 *
 * @return mysqli_result
 *
 * @throws Exception
 */
function getSubscriptionExpiringData(
    mysqli $mysqli,
    array  $daysSubscriptionExpiring,
    int    $limit = 1000
): mysqli_result
{
    $sql = 'SELECT 
                u.id
            FROM 
                 user u
			LEFT JOIN
				queue_user_subscription_expiration qu
                ON qu.user_id = u.id
            WHERE 
                valid = ?
                    AND
                {validtsTemplate}
					AND
				qu.id IS NULL
            ORDER BY validts DESC
            LIMIT ?';

    [$validtsSql, $validtsParams] = prepareValidtsSubscriptionExpiring($daysSubscriptionExpiring);

    $sql = str_replace('{validtsTemplate}', $validtsSql, $sql);

    $params = array_merge(
        [true],
        $validtsParams,
        [$limit]
    );

    $result = selectDb($mysqli, $sql, $params);

    if (!$result instanceof mysqli_result) {
        throw new Exception('[mysqli] Ошибка получения данных окончания подписки');
    }

    return $result;
}

/**
 * Добавляет данные о пользователях у которых заканчивается подписка в очередь
 *
 * @param mysqli $mysqli
 * @param array $userIds
 * @param int $maxGroup
 *
 * @return void
 *
 * @throws Exception
 */
function addToQueueUserSubscriptionExpiration(mysqli $mysqli, array $userIds, int $maxGroup = 5): void
{
    if (count($userIds) === 0) {
        return;
    }

    $sql = 'INSERT IGNORE INTO queue_user_subscription_expiration (user_id, group_id) VALUES ';
    $params = [];

    try {
        $lastUserKey = array_key_last($userIds);
        $groupId = 0;

        foreach ($userIds as $userKey => $userId) {
            $separator = ', ';

            if ($lastUserKey === $userKey) {
                $separator = ';';
            }

            $sql .= '(?, ?)' . $separator;
            $params[] = $userId;

            $groupId++;

            if ($groupId >= $maxGroup) {
                $groupId = 1;
            }

            $params[] = $groupId;
        }

        insertDb($mysqli, $sql, $params);

    } catch (Throwable $exception) {
        throw new Exception('[mysqli] Ошибка вставки данных в очередь');
    }
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

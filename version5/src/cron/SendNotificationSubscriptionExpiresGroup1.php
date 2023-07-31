<?php

declare(strict_types=1);

require '../../core/Core.php';

$mysqli = null;

try {
    $mysqli = connectDb();
    $mysqlResult = getQueueSubscriptionExpiringData($mysqli, 1);

    foreach ($mysqlResult as $result) {
        $queueId = $result['qu_id'] ?? '';
        $email = $result['email'] ?? '';
        $userName = $result['username'] ?? '';

        if ('' === $queueId || '' === $email || '' === $userName) {
            /**
             * Если email или userName пустые, то пропустить для них уведомление пользователя
             * Ответственно за валидность данных несёт другой сервис (прим: сервис регистрации пользователя).
             */
            continue;
        }

        try {
            sendEmailSubscriptionExpiring($email, $userName);

            deleteQueueId($mysqli, (int) $queueId);
        } catch (Throwable $exception) {
            /**
             * Выполнить логирование ошибки или ещё какие-то действия
             * Но при этом продолжить цикл отправки уведомлений
             */
        }
    }
} catch (Throwable $exception) {
    /** Выполнить логирование ошибки или ещё какие-то действия  */
    throw $exception;
} finally {
    closeDb($mysqli);
}

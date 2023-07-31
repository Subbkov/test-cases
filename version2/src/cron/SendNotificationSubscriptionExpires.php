<?php

declare(strict_types=1);

require '../../core/Core.php';

$mysqli = null;

try {
    $mysqli = connectDb();
    $mysqlResult = getSubscriptionExpiringData($mysqli, EMAIL_DAYS_SUBSCRIPTION_EXPIRING);

    foreach ($mysqlResult as $result) {
        $email = $result['email'] ?? '';
        $userName = $result['username'] ?? '';

        if ('' === $email || '' === $userName) {
            /**
             * Если email или userName пустые, то пропустить для них уведомление пользователя
             * Ответственно за валидность данных несёт другой сервис (прим: сервис регистрации пользователя).
             */
            continue;
        }

        try {
            sendToQueueSubscriptionExpiring($email, $userName);
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

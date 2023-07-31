<?php

declare(strict_types=1);

require '../../core/Core.php';

$mysqli = null;

try {
    $currentTimestamp = time();
    $mysqli = connectDb();
    $mysqlResult = getSubscriptionExpiringData($mysqli, EMAIL_DAYS_SUBSCRIPTION_EXPIRING, $currentTimestamp);

    foreach ($mysqlResult as $result) {
        $id = $result['id'] ?? '';
        $email = $result['email'] ?? '';
        $userName = $result['username'] ?? '';

        if ('' === $id || '' === $email || '' === $userName) {
            /**
             * Если email или userName пустые, то пропустить для них уведомление пользователя
             * Ответственно за валидность данных несёт другой сервис (прим: сервис регистрации пользователя).
             */
            continue;
        }

        try {
            sendEmailSubscriptionExpiring($email, $userName);

            updateLastSent($mysqli, (int) $id, $currentTimestamp);
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

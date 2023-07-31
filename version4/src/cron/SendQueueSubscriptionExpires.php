<?php

declare(strict_types=1);

require '../../core/Core.php';

$mysqli = null;

try {
    $mysqli = connectDb();
    $mysqlResult = getSubscriptionExpiringData($mysqli, EMAIL_DAYS_SUBSCRIPTION_EXPIRING);

    $ids = [];

    foreach ($mysqlResult as $result) {
        $id = $result['id'] ?? '';

        if ('' === $id) {
            /**
             * Если email или userName пустые, то пропустить для них уведомление пользователя
             * Ответственно за валидность данных несёт другой сервис (прим: сервис регистрации пользователя).
             */
            continue;
        }

        $ids[] = (int) $id;
    }

    if(count($ids) > 0){
        addToQueueUserSubscriptionExpiration($mysqli, $ids);
    }

} catch (Throwable $exception) {
    /** Выполнить логирование ошибки или ещё какие-то действия  */
    throw $exception;
} finally {
    closeDb($mysqli);
}

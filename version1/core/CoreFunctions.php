<?php

declare(strict_types=1);

/**
 * Подготавливает шаблон email для извещения пользователя об окончании подписки
 *
 * @param string $userName
 *
 * @return string
 */
function getSubscriptionExpiringMessage(string $userName): string
{
    return str_replace('{username}', $userName, EMAIL_TEMPLATE_SUBSCRIPTION_EXPIRING);
}

/**
 * Отправка Email.
 * Подготавливает для отправки данные и отправляет с помощью вендорной функции email
 *
 * @param string $email
 * @param string $userName
 *
 * @return void
 */
function sendEmailSubscriptionExpiring(string $email, string $userName): void
{
    try {
        $message = getSubscriptionExpiringMessage($userName);

        send_email(EMAIL_ADMIN, $email, $message);
    } catch (Throwable $exception) {
        /** Можно залогировать ошибку и сделать какие-то ещё действия или вообще ничего не делать */
    }
}

/**
 * Обёртка для вендорной функции проверки email
 *
 * @param string $email
 * @return void
 */
function checkEmail(string $email): void
{
    try {
        check_email($email);
    } catch (Throwable $exception) {
        /** Можно залогировать ошибку и сделать какие-то ещё действия или вообще ничего не делать */
    }
}

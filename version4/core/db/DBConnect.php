<?php

declare(strict_types=1);

/**
 * Выполняет подключение к БД
 *
 * @param string $host
 * @param int $port
 * @param string $name
 * @param string $user
 * @param string $pass
 *
 * @return mysqli
 */
function connectDb(
    string $host = _DB_HOST,
    int    $port = _DB_PORT,
    string $name = _DB_NAME,
    string $user = _DB_USER,
    string $pass = _DB_PASS
): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);

    $mysqli = mysqli_connect($host, $user, $pass, $name, $port);

    if (false === $mysqli || mysqli_connect_errno()) {
        throw new RuntimeException('[mysqli] Ошибка соединения: ' . mysqli_connect_error());
    }

    mysqli_set_charset($mysqli, 'utf8mb4');

    if (mysqli_errno($mysqli)) {
        throw new RuntimeException('[mysqli]: ' . mysqli_error($mysqli));
    }

    return $mysqli;
}

/**
 * Выполняет select к БД и возвращает результаты в виде объекта mysqli_result
 *
 * @param mysqli $mysqli
 * @param string $sql
 * @param array $params
 *
 * @return mysqli_result
 *
 * @throws Exception
 */
function selectDb(mysqli $mysqli, string $sql, array $params = []): mysqli_result
{
    $mysqliStmt = mysqli_prepare($mysqli, $sql);
    $params = prepareParamsDb($params);
    $typeParams = getTypeParamsDb($params);

    mysqli_stmt_bind_param($mysqliStmt, $typeParams, ...$params);

    mysqli_stmt_execute($mysqliStmt);

    $result = mysqli_stmt_get_result($mysqliStmt);

    if (!$result instanceof mysqli_result) {
        throw new Exception('[mysqli] Ошибка получения данных');
    }

    return $result;
}

/**
 * Подготавливает и выполняет запрос stmt
 *
 * @param mysqli $mysqli
 * @param string $sql
 * @param array $params
 *
 * @return mysqli_stmt
 *
 * @throws Exception
 */
function prepareChangesDb(mysqli $mysqli, string $sql, array $params = []): mysqli_stmt
{
    $mysqliStmt = mysqli_prepare($mysqli, $sql);
    $params = prepareParamsDb($params);
    $typeParams = getTypeParamsDb($params);

    mysqli_stmt_bind_param($mysqliStmt, $typeParams, ...$params);

    mysqli_stmt_execute($mysqliStmt);

    return $mysqliStmt;
}


/**
 * Обновляет записи
 *
 * @param mysqli $mysqli
 * @param string $sql
 * @param array $params
 *
 * @return int
 *
 * @throws Exception
 */
function updateDb(mysqli $mysqli, string $sql, array $params): int
{
    $mysqliStmt = prepareChangesDb($mysqli, $sql, $params);

    $error = mysqli_stmt_error($mysqliStmt);

    $affectedRows = mysqli_stmt_affected_rows($mysqliStmt);

    mysqli_stmt_close($mysqliStmt);

    if ('' !== $error) {
        throw new Exception('[mysqli] Ошибка обновления данных');
    }

    return $affectedRows;
}

/**
 * Удаляет записи
 *
 * @param mysqli $mysqli
 * @param string $sql
 * @param array $params
 *
 * @return int
 *
 * @throws Exception
 */
function deleteDb(mysqli $mysqli, string $sql, array $params): int
{
    $mysqliStmt = prepareChangesDb($mysqli, $sql, $params);

    $error = mysqli_stmt_error($mysqliStmt);

    $affectedRows = mysqli_stmt_affected_rows($mysqliStmt);

    mysqli_stmt_close($mysqliStmt);

    if ('' !== $error) {
        throw new Exception('[mysqli] Ошибка удаления данных');
    }

    return $affectedRows;
}

/**
 * Вставляет новые записи
 *
 * @param mysqli $mysqli
 * @param string $sql
 * @param array $params
 *
 * @return int
 *
 * @throws Exception
 */
function insertDb(mysqli $mysqli, string $sql, array $params): int
{
    $mysqliStmt = prepareChangesDb($mysqli, $sql, $params);

    $error = mysqli_stmt_error($mysqliStmt);

    $affectedRows = mysqli_stmt_affected_rows($mysqliStmt);

    mysqli_stmt_close($mysqliStmt);

    if ('' !== $error) {
        throw new Exception('[mysqli] Ошибка добавления данных');
    }

    return $affectedRows;
}

/**
 * Подготавливает параметры для "биндинга"
 *
 * @param array $params
 *
 * @return array
 */
function prepareParamsDb(array $params): array
{
    foreach ($params as &$param) {
        if (is_bool($param)) {
            $param = (int)$param;
        }
    }

    return $params;
}

/**
 * Возвращает тип всех параметров в виде строки для "Биндинга"
 *
 * @param array $params
 *
 * @return string
 *
 * @throws Exception
 */
function getTypeParamsDb(array $params): string
{
    $result = '';

    foreach ($params as $param) {
        $result .= getTypeParamDb($param);
    }

    return $result;

}

/**
 * Определяет тип передаваемого параметра, и возвращает тип для "биндинга"
 *
 * @param $param
 *
 * @return string
 *
 * @throws Exception
 */
function getTypeParamDb($param): string
{
    if (is_string($param)) {
        return 's';
    }

    if (is_int($param)) {
        return 'i';
    }

    if (is_float($param)) {
        return 'd';
    }

    throw new Exception('[mysqli] Передан не верный тип параметра. Допустимые типы: string, integer, float');
}

/**
 * Закрывает соединение с БД
 *
 * @param mysqli $mysqli
 *
 * @return void
 */
function closeDb(mysqli $mysqli): void
{
    mysqli_close($mysqli);
}

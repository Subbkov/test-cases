<?php

declare(strict_types=1);


require '../core/Core.php';


try {
    $con = connectDb();

    $id = 0;

    $lastMysqliResult = $con->query("SELECT * FROM user ORDER BY id DESC LIMIT 1");

    if ($lastMysqliResult instanceof mysqli_result && $lastMysqliResult->num_rows > 0) {
        $iterator = $lastMysqliResult->getIterator();
        $iterator->rewind();
        if (!$iterator->valid()) {
            throw new Exception('There is no any element!');
        }
        $firstElement = $iterator->current();

        $id = $firstElement['id'] ?? 0;
    }


    mysqli_autocommit($con, false);

    $id++;

    for (; $id <= 500000;) {

        mysqli_begin_transaction($con);


        for ($i = 1; $i <= 100; $i++) {

            $timestamp = time();
            $time = $id * 100;
            $fullTimestamp = $timestamp + $time;

            if ($fullTimestamp > ((86400 * 15) + $timestamp)) {
                $fullTimestamp = 0;
            }


            $confirmed = mt_rand(0, 1);
            $checked = mt_rand(0, 1);
            $valid = mt_rand(0, 1);

            $tt = $con->query(
                "INSERT INTO `user` (`id`, `username`, `email`, `validts`, `confirmed`, `checked`, `valid`) 
                    VALUES ('" . $id . "', 'username" . $id . "', 'email" . $id . "', " . $fullTimestamp . ", " . $confirmed . ", " . $checked . ", " . $valid . ");");

            $id++;
        }
        mysqli_commit($con);
    }

} catch (Throwable $exception) {
    $tr = 1;
} finally {
    closeDb($con);
}

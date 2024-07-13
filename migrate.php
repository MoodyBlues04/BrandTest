<?php

require_once __DIR__ . '/db.php';
global $mysqli;

$migrations = [
    'CREATE TABLE `article_views` (
       `article_id` int(10) unsigned NOT NULL,
       `user_id` int(10) unsigned NOT NULL DEFAULT 0,
       `date` date,
       `count_views` smallint(6) unsigned NOT NULL DEFAULT 0,
       PRIMARY KEY (`article_id`, `user_id`, `date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
];

foreach ($migrations as $migration) {
    execQuery($migration);
}
$mysqli->close();

<?php

require_once __DIR__ . '/db.php';

function addView(int $userId, int $articleId, string $date): void
{
    execQuery("INSERT INTO article_views
                        (article_id, user_id, date, count_views)
                        VALUES ($articleId, $userId, '$date', 1)
                      ON DUPLICATE KEY UPDATE count_views = count_views + 1;");
}

for ($userId = 1; $userId < 1000; $userId++) {
    echo $userId . PHP_EOL;
    for ($day = 2; $day < 1000; $day++) {
        $articleId = random_int(1, 40);
        $date = date('Y-m-d', strtotime("-{$day} days"));
        addView($userId, $articleId, $date);
    }
}

<?php

require_once __DIR__ . '/db.php';

/**
 * Adding index on pair (user_id, date) should optimize execution on group by (user_id, date)
 */
function migrateForOptimization(bool $enable = true): void
{
    if ($enable)
        execQuery("ALTER TABLE article_views ADD INDEX (user_id, date);");
    else
        execQuery("DROP INDEX user_id ON article_views;");
}

function selectGroupedByDateAndUser(): array
{
    return select("SELECT date, user_id, SUM(count_views) AS views
                        FROM article_views
                        GROUP BY user_id, date");
}

function calcAvgTime(int $attempts = 10): float
{
    $timeSum = 0;
    for ($i = 0; $i < $attempts; $i++) {
        $timeStart = millisec();
        selectGroupedByDateAndUser();
        $timeSum += millisec() - $timeStart;
    }
    return $timeSum / (float)$attempts;
}
function millisec(): int
{
    return floor(microtime(true) * 1000);
}

// Speed test: 1million records, 550k unique in group by, 12.3 sec with no INDEX, 10.87 with index
var_dump(calcAvgTime());
//migrateForOptimization(false);


<?php

$mysqli = new mysqli(
    'localhost',
    'root',
    '',
    'brand_test'
);
if ($mysqli->connect_error) {
    die("Connection failed: $mysqli->connect_error");
}

function select(string $query): array
{
    global $mysqli;
    $result = $mysqli->query($query);

    $res = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $res []= $row;
        }
    }
    return $res;
}

/**
 * @throws Exception
 */
function execQuery(string $query): void
{
    global $mysqli;
    if (!$mysqli->execute_query($query)) {
        throw new \Exception("Query execution failed with message: {$mysqli->error}");
    }
}
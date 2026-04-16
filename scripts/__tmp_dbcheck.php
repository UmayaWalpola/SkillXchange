<?php
$mysqli = new mysqli('localhost', 'root', '', 'skillxchange', 3306);
if ($mysqli->connect_error) {
    echo 'CONNECT_ERROR:' . $mysqli->connect_error;
    exit(1);
}
$res = $mysqli->query('SHOW COLUMNS FROM quizzes');
if (!$res) {
    echo 'ERROR:' . $mysqli->error;
    exit(1);
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . '|' . $row['Type'] . "\n";
}

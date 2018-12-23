<?php
/**
 * Created by PhpStorm.
 * User: AYINDE
 * Date: 08/12/2018
 * Time: 10:42
 */

$dsn = 'mysql:host=localhost; dbname=student_complaint';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo 'connection error! ' . $e->getMessage();
}
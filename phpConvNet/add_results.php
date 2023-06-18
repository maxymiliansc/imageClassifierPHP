<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'image_data';


$connection = new mysqli($host, $user, $password, $database);
if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}


$trainAccuracy = $_POST['trainAccuracy'];
$testAccuracy = $_POST['testAccuracy'];
$params = $_POST['params'];
$date = date('Y-m-d H:i:s');
$identifier = mt_rand();


$query = "INSERT INTO results (identifier, train_acc, test_acc, date, params) VALUES ('$identifier', '$trainAccuracy', '$testAccuracy', '$date', '$params')";
if ($connection->query($query) === TRUE) {
    echo 'Model results added successfully. Identifier: ' . $identifier;
} else {
    echo 'Error adding model results: ' . $connection->error;
}


$connection->close();
?>

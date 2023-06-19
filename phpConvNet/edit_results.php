<?php

include 'db_connect.php';


$identifier = $_POST['identifier'];
$trainAccuracy = $_POST['trainAccuracy'];
$testAccuracy = $_POST['testAccuracy'];
$date = $_POST['date'];
$params = $_POST['params'];


$query = "UPDATE results SET train_acc = '$trainAccuracy', test_acc = '$testAccuracy', date = '$date', params = '$params' WHERE identifier = $identifier";


if ($conn->query($query) === TRUE) {

    echo 'Results updated successfully';
} else {

    echo 'Error updating results: ' . $conn->error;
}


$conn->close();
?>

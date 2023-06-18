<?php

include 'db_connect.php';


$identifier = $_POST['identifier'];
$trainAccuracy = $_POST['trainAccuracy'];
$testAccuracy = $_POST['testAccuracy'];
$date = $_POST['date'];
$params = $_POST['params'];


$query = "UPDATE results SET train_acc = '$trainAccuracy', test_acc = '$testAccuracy', date = '$date', params = '$params' WHERE identifier = $identifier";


if ($conn->query($query) === TRUE) {
    // Success
    echo 'Results updated successfully';
} else {
    // Error
    echo 'Error updating results: ' . $conn->error;
}


$conn->close();
?>

<?php

$identifier = $_POST['identifier'];

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'image_data';


$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$response = [];

if (!empty($identifier)) {

    $query = "DELETE FROM results WHERE identifier = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $identifier);


    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Results deleted successfully';
    } else {
        $response['success'] = false;
        $response['message'] = 'Error deleting results: ' . $stmt->error;
    }


    $stmt->close();
} else {
    $response['success'] = false;
    $response['message'] = 'Identifier is missing or empty';
}


header('Content-Type: application/json');
echo json_encode($response);


$conn->close();
?>

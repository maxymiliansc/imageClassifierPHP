<?php
require_once 'db_connect.php';

$identifier = $_POST['identifier'];
$response = [];

if (!empty($identifier)) {
    $query = "DELETE FROM results WHERE identifier = ?";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(1, $identifier, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $response['success'] = true;
        $response['message'] = 'Results deleted successfully';
    } catch(PDOException $e) {
        $response['success'] = false;
        $response['message'] = 'Error deleting results: ' . $e->getMessage();
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Identifier is missing or empty';
}

header('Content-Type: application/json');
echo json_encode($response);
?>

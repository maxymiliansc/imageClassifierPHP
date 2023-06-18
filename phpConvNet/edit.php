<?php
require_once 'EditManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageId = $_POST['id'];
    $label = $_POST['label'];
    $name = $_POST['name'];
    $isTrain = $_POST['isTrain'];

    $editManager = new EditManager($conn, $isTrain);
    $editManager->editImage($imageId, $label, $name);

    http_response_code(200);
    exit();
} else {
    http_response_code(400);
    exit();
}
?>

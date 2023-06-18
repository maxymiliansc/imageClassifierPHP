<?php
require_once 'DeleteManager.php';

if (isset($_POST['id'])) {
    $imageId = $_POST['id'];
    $isTrain = $_POST['isTrain'];

    $deleteManager = new DeleteManager($conn, $isTrain);
    $deleteManager->deleteImage($imageId);
}
?>

<?php

if (isset($_POST['label']) && isset($_POST['name']) && isset($_FILES['image'])) {
    $label = $_POST['label'];
    $name = $_POST['name'];
    $image = $_FILES['image'];
    $isTrain = $_POST['isTrain'];
    // Ensure the directory exists
    $directory = 'PetImages/User';
    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }


    $filename = uniqid() . '_' . $name . '.jpg';


    $allowedTypes = ['image/jpeg'];
    if (!in_array($image['type'], $allowedTypes)) {
        // Invalid file type
        http_response_code(400);
        echo 'Invalid file type. Only JPG images are allowed.';
        exit;
    }


    $destination = $directory . '/' . $filename;
    if (move_uploaded_file($image['tmp_name'], $destination)) {

        $imageData = imagecreatefromjpeg($destination);


        $rotatedImage = imagerotate($imageData, -90, 0);


        imagejpeg($rotatedImage, $destination, 90);

        imagedestroy($imageData);
        imagedestroy($rotatedImage);




        require 'db_connect.php';
        $table = $isTrain ? 'train_images' : 'test_images';
        $labelTable = $isTrain ? 'train_labels' : 'test_labels';

        $stmt = $conn->prepare("INSERT INTO $labelTable (label) VALUES (?)");
        $stmt->execute([$label]);
        $imgId = $conn->lastInsertId();


        $stmt = $conn->prepare("INSERT INTO $table (name, path, img_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $destination, $imgId]);


        http_response_code(200);
        echo 'Image added successfully';
    } else {

        http_response_code(500);
        echo 'There was an error adding the image';
    }
} else {

    http_response_code(400);
    echo 'Invalid request';
}
?>

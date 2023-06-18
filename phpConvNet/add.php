<?php
// Check if the request contains the necessary data
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

    // Generate a unique filename for the image
    $filename = uniqid() . '_' . $name . '.jpg'; // Append '.jpg' extension

    // Validate file type
    $allowedTypes = ['image/jpeg'];
    if (!in_array($image['type'], $allowedTypes)) {
        // Invalid file type
        http_response_code(400);
        echo 'Invalid file type. Only JPG images are allowed.';
        exit;
    }

    // Move the uploaded image to the directory with the generated filename
    $destination = $directory . '/' . $filename;
    if (move_uploaded_file($image['tmp_name'], $destination)) {
        // Convert the image to JPG format
        $imageData = imagecreatefromjpeg($destination);

        // Rotate the image by 90 degrees to the right
        $rotatedImage = imagerotate($imageData, -90, 0);

        // Save the rotated image as JPG
        imagejpeg($rotatedImage, $destination, 90);

        // Free up memory
        imagedestroy($imageData);
        imagedestroy($rotatedImage);

        // Image rotated and saved successfully

        // Database operations
        require 'db_connect.php'; // Include your db_connect file or adjust the path accordingly
        $table = $isTrain ? 'train_images' : 'test_images';
        $labelTable = $isTrain ? 'train_labels' : 'test_labels';
        // Insert the label into the $labelTable
        $stmt = $conn->prepare("INSERT INTO $labelTable (label) VALUES (?)");
        $stmt->execute([$label]);
        $imgId = $conn->lastInsertId();

        // Insert the image details into the $imgTable
        $stmt = $conn->prepare("INSERT INTO $table (name, path, img_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $destination, $imgId]);

        // Return a success response
        http_response_code(200);
        echo 'Image added successfully';
    } else {
        // Failed to move the uploaded image
        // Return an error response
        http_response_code(500);
        echo 'There was an error adding the image';
    }
} else {
    // Invalid request, missing required data
    // Return an error response
    http_response_code(400);
    echo 'Invalid request';
}
?>

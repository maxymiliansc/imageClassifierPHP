<?php
// edit.php

require_once 'db_connect.php';

function getImageById($imageId, $conn) {
    $stmt = $conn->prepare("SELECT path, label FROM train_images INNER JOIN train_labels ON train_images.img_id = train_labels.id WHERE train_images.img_id = ?");
    $stmt->execute([$imageId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if the image ID is provided
if (!isset($_GET['id'])) {

    header("Location: train_images.php");
    exit();
}

$imageId = $_GET['id'];
echo (var_dump($imageId));


$image = getImageById($imageId, $conn);


if (!$image) {

    header("Location: train_images.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the new label from the submitted form
    $newLabel = $_POST['label'];

    // Update the image label in the database
    $stmt = $conn->prepare("UPDATE train_labels SET label = ? WHERE id = ?");
    $stmt->execute([$newLabel, $imageId]);

    // Redirect to the image gallery or relevant page
    header("Location: train_images.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Image</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Image</h1>

    <form method="POST" action="train_images.php">
        <div class="form-group">
            <label for="label">Label</label>
            <input type="text" class="form-control" id="label" name="label" value="<?php echo $image['label']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
</body>
</html>

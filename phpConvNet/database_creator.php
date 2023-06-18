<?php
require_once 'db_connect.php';

$conn->exec("CREATE DATABASE IF NOT EXISTS php_image_data");

// USE php_image_data;
$conn->exec("USE php_image_data");

$conn->exec("CREATE TABLE IF NOT EXISTS train_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label INT
)");


$conn->exec("CREATE TABLE IF NOT EXISTS test_labels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label INT
)");


$conn->exec("CREATE TABLE IF NOT EXISTS train_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    path text,
    img_id INT,
    FOREIGN KEY (img_id) REFERENCES train_labels(id)
)");


$conn->exec("CREATE TABLE IF NOT EXISTS test_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    path text,
    img_id INT,
    FOREIGN KEY (img_id) REFERENCES test_labels(id)
)");

$conn->exec("CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    iteration INT,
    train_acc FLOAT,
    test_acc FLOAT,
    date DATE,
    params text
)");


$folderPathDog = 'PetImages/Dog';
$folderPathCat = 'PetImages/Cat';

$imagesDog = glob($folderPathDog."/*.{jpg,jpeg,png}", GLOB_BRACE);
$imagesCat = glob($folderPathCat."/*.{jpg,jpeg,png}", GLOB_BRACE);

$images = array_merge($imagesDog, $imagesCat);

$labels = array_merge(array_fill(0, count($imagesDog), 0), array_fill(0, count($imagesCat), 1));

$indices = range(0, count($images) - 1);
shuffle($indices);
$shuffledImages = [];
$shuffledLabels = [];

foreach ($indices as $index) {
    $shuffledImages[] = $images[$index];
    $shuffledLabels[] = $labels[$index];
}

$images = $shuffledImages;
$labels = $shuffledLabels;

$testSplitIndex = (int)(0.9 * count($images));

$trainImages = array_slice($images, 0, $testSplitIndex);
$trainLabels = array_slice($labels, 0, $testSplitIndex);
$testImages = array_slice($images, $testSplitIndex);
$testLabels = array_slice($labels, $testSplitIndex);

function storeImagesAndLabels($images, $labels, $isTrain, $conn) {
    $imgTable = $isTrain ? 'train_images' : 'test_images';
    $labelTable = $isTrain ? 'train_labels' : 'test_labels';

    for ($i = 0; $i < count($images); $i++) {
        $imgName = basename($images[$i]);

        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO $labelTable (label) VALUES (?)");
        $stmt->execute([$labels[$i]]);
        $imgId = $conn->lastInsertId();

        $stmt = $conn->prepare("INSERT INTO $imgTable (name, path, img_id) VALUES (?, ?, ?)");
        $stmt->execute([$imgName, $images[$i], $imgId]);

        $conn->commit();
    }
}

storeImagesAndLabels($trainImages, $trainLabels, true, $conn);
storeImagesAndLabels($testImages, $testLabels, false, $conn);

?>


<?php
require_once 'db_connect.php';

function getImagesAndLabels($isTrain, $conn) {
    $imgTable = $isTrain ? 'train_images' : 'test_images';
    $labelTable = $isTrain ? 'train_labels' : 'test_labels';

    $stmt = $conn->prepare("SELECT $imgTable.path, $labelTable.label FROM $imgTable INNER JOIN $labelTable ON $imgTable.img_id = $labelTable.id");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $images = [];
    $labels = [];

    foreach ($results as $result) {
        $images[] = $result['path'];
        $labels[] = $result['label'];
    }

    return [$images, $labels];
}

// Retrieve the train images and labels from the database
[$trainImages, $trainLabels] = getImagesAndLabels(true, $conn);

// Pagination settings
$maxPerPage = 20;
$totalImages = count($trainImages);
$totalPages = ceil($totalImages / $maxPerPage);
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$startIndex = ($currentPage - 1) * $maxPerPage;
$visibleImages = array_slice($trainImages, $startIndex, $maxPerPage);
$visibleLabels = array_slice($trainLabels, $startIndex, $maxPerPage);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Training</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #b1ecfe;
        }
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            color: #555;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #d4a5a5;
            border-color: #d4a5a5;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #b98686;
            border-color: #b98686;
        }

        .masonry {
            column-count: 4;
            column-gap: 2em;
        }

        .item {
            border-radius: 10px;
            display: inline-block;
            background: #fff;
            margin: 0 0 1em;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 2px 2px 4px 0 rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .item:hover {
            transform: scale(1.05);
        }

        .item img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .item .label {
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 14px;
            color: #fff;
            background: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .item:hover .label {
            opacity: 1;
        }

        .item .options {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            padding: 10px;
            border-radius: 0 0 10px 10px;
            display: flex;
            justify-content: space-between;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .item:hover .options {
            opacity: 1;
        }

        .item .options a {
            color: #fff;
            margin-right: 10px;
            font-size: 14px;
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .pagination .page-link {
            background-color: #d4a5a5;
            border-color: #d4a5a5;
            color: #fff;
        }

        .pagination .page-link:hover {
            background-color: #b98686;
            border-color: #b98686;
        }

        .pagination .page-item.active .page-link {
            background-color: #b98686;
            border-color: #b98686;
        }

        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center my-3">
        <h1>Image Gallery</h1>
        <a href="add_image.php" class="btn btn-primary">Add Image</a>
    </div>
    <div class="masonry">
        <?php
        foreach ($visibleImages as $index => $image) {
            $label = $visibleLabels[$index] == 0 ? 'Dog' : 'Cat';
            echo '<div class="item">';
            echo '<img src="' . $image . '" alt="Image">';
            echo '<div class="label">' . $label . '</div>';
            echo '<div class="options">';
            echo '<a href="edit.php?id=' . $index . '">Edit Label</a>';
            echo '<a href="delete.php?id=' . $index . '">Delete Image</a>';
            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php
            // Calculate the range of pages to display
            $startPage = max(1, $currentPage - 2);
            $endPage = min($startPage + 4, $totalPages);

            for ($i = $startPage; $i <= $endPage; $i++) {
                echo '<li class="page-item';
                if ($i === $currentPage) {
                    echo ' active';
                }
                echo '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
            }
            ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
</body>
</html>

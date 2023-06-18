<?php
require_once 'db_connect.php';
require_once 'ImageManager.php';
require_once 'paginate.php';

$imageManager = new ImageManager(false, $conn);


[$testImages, $testLabels] = $imageManager->getImagesAndLabels();




$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$maxPerPage = 20;
[$visibleImages, $visibleLabels, $totalPages] = paginateImages($testImages, $testLabels, $currentPage, $maxPerPage);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Test images</title>

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

        .item .name {
            position: absolute;
            bottom: 30px;
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

        .item:hover .name {
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
        <a href="#" onclick="addImage()" class="btn btn-primary">Add Image</a>
    </div>
    <div class="masonry">
        <?php
        foreach ($visibleImages as $index => $image) {
            $imageId = $image['id']; // Get the image ID
            $label = $visibleLabels[$index] == 0 ? 'Dog' : 'Cat';
            echo '<div class="item" data-id="' . $imageId . '">';
            echo '<img src="' . $image['path'] . '" alt="Image">';
            echo '<div class="label">' . $label . '</div>';
            echo '<div class="name">' . $image['name'] . '</div>';
            echo '<div class="options">';
            echo '<a href="#" class="edit-label" data-id="' . $imageId . '">Edit</a>';
            echo '<a href="#" class="delete-image" data-id="' . $imageId . '">Delete Image</a>';
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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function addImage() {
        Swal.fire({
            title: 'Enter the image information',
            html:
                '<input id="swal-label" class="swal2-input" placeholder="Label">' +
                '<input id="swal-name" class="swal2-input" placeholder="Name">' +
                '<input id="swal-image" type="file" class="swal2-input">',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                var label = $('#swal-label').val();
                var name = $('#swal-name').val();
                var image = $('#swal-image').prop('files')[0];

                var labelValue;

                if (label && name && image) {
                    if (label.toLowerCase() === 'cat') {
                        labelValue = 1;
                    } else if (label.toLowerCase() === 'dog') {
                        labelValue = 0;
                    } else {
                        Swal.fire('Error!', 'Invalid label value. Please enter "Cat" or "Dog".', 'error');
                        return false;
                    }

                    var data = new FormData();
                    data.append('label', labelValue);
                    data.append('name', name);
                    data.append('image', image);
                    data.append('isTrain', 0);

                    return $.ajax({
                        url: 'add.php',
                        method: 'POST',
                        processData: false,
                        contentType: false,
                        data: data,
                    })
                        .done(function() {
                            Swal.fire('Success!', 'Image added successfully', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        })
                        .fail(function() {
                            Swal.fire('Error!', 'There was an error adding the image', 'error');
                        });
                }
            },
            allowOutsideClick: () => !Swal.isLoading(),
        });
    }
    $(document).ready(function() {
        $('.edit-label').click(function(event) {
            event.preventDefault();

            var imageId = $(this).data('id');

            Swal.fire({
                title: 'Enter the new label and name',
                html:
                    '<input id="swal-label" class="swal2-input" placeholder="Label" value="' +
                    $('.item[data-id="' + imageId + '"] .label').text() +
                    '">' +
                    '<input id="swal-name" class="swal2-input" placeholder="Name" value="' +
                    $('.item[data-id="' + imageId + '"] .name').text() +
                    '">',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    var label = $('#swal-label').val();
                    var name = $('#swal-name').val();
                    var labelValue;

                    if (label && name) {
                        if (label.toLowerCase() === 'cat') {
                            labelValue = 1;
                        } else if (label.toLowerCase() === 'dog') {
                            labelValue = 0;
                        } else {
                            Swal.fire('Error!', 'Invalid label value. Please enter "Cat" or "Dog".', 'error');
                            return false;
                        }

                        return $.ajax({
                            url: 'edit.php',
                            method: 'POST',
                            data: {
                                id: imageId,
                                label: labelValue,
                                name: name,
                                isTrain: 0,
                            },
                        })
                            .done(function() {
                                Swal.fire('Success!', 'Label and name updated successfully', 'success');

                                $('.item[data-id="' + imageId + '"] .label').text(label);
                                $('.item[data-id="' + imageId + '"] .name').text(name);
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            })
                            .fail(function() {
                                Swal.fire('Error!', 'There was an error updating the label and name', 'error');
                            });
                    }
                },
                allowOutsideClick: () => !Swal.isLoading(),
            });
        });

        $('.delete-image').click(function(event) {
            event.preventDefault();

            var imageId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure you want to delete this image?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Delete',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return $.ajax({
                        url: 'delete.php',
                        method: 'POST',
                        data: {
                            id: imageId,
                            isTrain: 0,
                        },
                    })
                        .done(function() {
                            Swal.fire('Success!', 'Image deleted successfully', 'success');
                            $('.item[data-id="' + imageId + '"]').remove();
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        })
                        .fail(function() {
                            Swal.fire('Error!', 'There was an error deleting the image', 'error');
                        });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            });
        });
    });
</script>
</body>
</html>


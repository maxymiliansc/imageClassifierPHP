<!DOCTYPE html>
<html>
<head>
    <title>Results</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.0/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.0/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            background-color: #b1ecfe;
        }

        .container {
            padding-top: 50px;
        }

        .result-table {
            border-collapse: collapse;
        }

        .result-table th,
        .result-table td {
            background-color: #dee2e6;
            border: 1px solid #1c86fd;
            padding: 8px;
        }

        .result-table th {
            background-color: #e192a7;
            font-weight: bold;
            text-align: center;
        }

        .result-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .btn-edit {
            background-color: #81cfe0;
            border-color: #81cfe0;
            color: #fff;
        }

        .btn-delete {
            background-color: #e86060;
            border-color: #e86060;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center mb-4">Results</h1>
    <table class="table result-table">
        <thead class="thead-light">
        <tr>
            <th scope="col">Identifier</th>
            <th scope="col">Train Accuracy</th>
            <th scope="col">Test Accuracy</th>
            <th scope="col">Date</th>
            <th scope="col">Params</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Database credentials
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $database = 'image_data';

        // Connect to the database
        $connection = new mysqli($host, $user, $password, $database);
        if ($connection->connect_error) {
            die('Connection failed: ' . $connection->connect_error);
        }


        $query = 'SELECT * FROM results';
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['identifier'] . '</td>';
                echo '<td>' . $row['train_acc'] . '</td>';
                echo '<td>' . $row['test_acc'] . '</td>';
                echo '<td>' . $row['date'] . '</td>';
                echo '<td>' . $row['params'] . '</td>';
                echo '<td>';
                echo '<a href="#" class="btn btn-edit btn-sm" data-identifier="' . $row['identifier'] . '" data-train-accuracy="' . $row['train_acc'] . '" data-test-accuracy="' . $row['test_acc'] . '" data-date="' . $row['date'] . '" data-params="' . $row['params'] . '">Edit</a> ';
                echo '<button class="btn btn-delete btn-sm" data-identifier="' . $row['identifier'] . '">Delete</button>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="6">No results found</td></tr>';
        }


        $connection->close();
        ?>
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('.btn-edit').click(function(event) {
            event.preventDefault();

            var identifier = $(this).data('identifier');
            var trainAccuracy = $(this).data('train-accuracy');
            var testAccuracy = $(this).data('test-accuracy');
            var date = $(this).data('date');
            var params = $(this).data('params');

            Swal.fire({
                title: 'Edit Results',
                html:
                    '<div class="swal2-content">' +
                    '<label for="train-accuracy-input">Train Accuracy:</label>' +
                    '<input id="train-accuracy-input" class="swal2-input" type="text" value="' + trainAccuracy + '">' +
                    '</div>' +
                    '<div class="swal2-content">' +
                    '<label for="test-accuracy-input">Test Accuracy:</label>' +
                    '<input id="test-accuracy-input" class="swal2-input" type="text" value="' + testAccuracy + '">' +
                    '</div>' +
                    '<div class="swal2-content">' +
                    '<label for="date-input">Date:</label>' +
                    '<input id="date-input" class="swal2-input" type="text" value="' + date + '">' +
                    '</div>' +
                    '<div class="swal2-content">' +
                    '<label for="params-input">Params:</label>' +
                    '<input id="params-input" class="swal2-input" type="text" value="' + params + '">' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Save',
                showLoaderOnConfirm: true,
                customClass: {
                    container: 'swal2-container',
                    content: 'swal2-content',
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    var editedTrainAccuracy = $('#train-accuracy-input').val();
                    var editedTestAccuracy = $('#test-accuracy-input').val();
                    var editedDate = $('#date-input').val();
                    var editedParams = $('#params-input').val();

                    return $.ajax({
                        url: 'edit_results.php',
                        method: 'POST',
                        data: {
                            identifier: identifier,
                            trainAccuracy: editedTrainAccuracy,
                            testAccuracy: editedTestAccuracy,
                            date: editedDate,
                            params: editedParams
                        },
                    })
                        .done(function() {
                            Swal.fire('Success!', 'Results updated successfully', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        })
                        .fail(function() {
                            Swal.fire('Error!', 'There was an error updating the results', 'error');
                        });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            });
        });

        $('.btn-delete').click(function(event) {
            event.preventDefault();

            var identifier = $(this).data('identifier');

            Swal.fire({
                title: 'Delete Results',
                text: 'Are you sure you want to delete these results?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                showLoaderOnConfirm: true,
                customClass: {
                    container: 'swal2-container',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {

                    return $.ajax({
                        url: 'delete_results.php',
                        method: 'POST',
                        data: {
                            identifier: identifier
                        },
                    })
                        .done(function(response) {
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        })
                        .fail(function() {
                            Swal.fire('Error!', 'There was an error deleting the results', 'error');
                        });
                },
                allowOutsideClick: () => !Swal.isLoading(),
            });
        });
    });

</script>

</body>
</html>

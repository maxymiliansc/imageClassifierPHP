<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bootstrap Site</title>

    <!-- Load bootstrap and jQuery from CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body {
            display: grid;
            grid-template-rows: repeat(3, 1fr);
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .carousel-item {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.5s;
            height: 100%;
        }

        .carousel-item:hover {
            transform: scale(1.05);
        }

        .carousel-item::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            transition: background 0.5s;
        }

        .carousel-item:hover::before {
            background: rgba(0, 0, 0, 0);
        }

        .bg-pastel1 {
            background-color: #b1ecfe; /* Soft Peach */
        }

        .bg-pastel2 {
            background-color: #F5FFFA; /* Mint Cream */
        }

        .bg-pastel3 {
            background-color: #ffc8d8; /* Lavender Blush */
        }

        h1 {
            color: #333;
            font-size: 4em;
            z-index: 1;
        }

        .carousel-item a {
            text-decoration: none;
            color: inherit;
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>
<div class="carousel-item bg-pastel1">
    <a href="images.php"><h1>Images</h1></a>
</div>
<div class="carousel-item bg-pastel2">
    <a href="model.php"><h1>Model</h1></a>
</div>
<div class="carousel-item bg-pastel3">
    <a href="history.php"><h1>History</h1></a>
</div>
</body>

</html>

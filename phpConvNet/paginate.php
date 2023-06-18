<?php
function paginateImages($images, $labels, $currentPage, $maxPerPage) {
    $totalImages = count($images);
    $totalPages = ceil($totalImages / $maxPerPage);
    $startIndex = ($currentPage - 1) * $maxPerPage;
    $visibleImages = array_slice($images, $startIndex, $maxPerPage);
    $visibleLabels = array_slice($labels, $startIndex, $maxPerPage);

    return [$visibleImages, $visibleLabels, $totalPages];
}

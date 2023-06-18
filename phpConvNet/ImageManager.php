<?php
require_once 'db_connect.php';

class ImageManager
{
    private $isTrain;
    private $conn;

    public function __construct($isTrain, $conn)
    {
        $this->isTrain = $isTrain;
        $this->conn = $conn;
    }

    public function getImagesAndLabels()
    {
        $imgTable = $this->isTrain ? 'train_images' : 'test_images';
        $labelTable = $this->isTrain ? 'train_labels' : 'test_labels';

        $stmt = $this->conn->prepare("SELECT $imgTable.id, $imgTable.path, $imgTable.name, $labelTable.label FROM $imgTable INNER JOIN $labelTable ON $imgTable.img_id = $labelTable.id");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $images = [];
        $labels = [];

        foreach ($results as $result) {
            $images[] = [
                'id' => $result['id'],
                'path' => $result['path'],
                'name' => $result['name'],
            ];
            $labels[] = $result['label'];
        }

        return [$images, $labels];
    }
}
?>


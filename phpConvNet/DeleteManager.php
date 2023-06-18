<?php
require_once 'db_connect.php';

class DeleteManager
{
    private $conn;
    private $table;

    public function __construct($conn, $isTrain)
    {
        $this->conn = $conn;
        $this->table = $isTrain ? 'train' : 'test';
    }

    public function deleteImage($imageId)
    {
        // Delete image from the database
        $stmt = $this->conn->prepare("DELETE FROM {$this->table}_images WHERE id = :id");
        $stmt->bindParam(':id', $imageId);
        $stmt->execute();

        // Delete label from the database
        $stmt = $this->conn->prepare("DELETE FROM {$this->table}_labels WHERE id = :id");
        $stmt->bindParam(':id', $imageId);
        $stmt->execute();
    }
}
?>

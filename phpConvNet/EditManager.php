<?php
require_once 'db_connect.php';

class EditManager
{
    private $conn;
    private $table;

    public function __construct($conn, $isTrain)
    {
        $this->conn = $conn;
        $this->table = $isTrain ? 'train' : 'test';
    }

    public function editImage($imageId, $label, $name)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table}_labels SET label = ? WHERE id = ?");
        $stmt->execute([$label, $imageId]);

        $stmt = $this->conn->prepare("UPDATE {$this->table}_images SET name = ? WHERE id = ?");
        $stmt->execute([$name, $imageId]);

        http_response_code(200);
        exit();
    }
}
?>


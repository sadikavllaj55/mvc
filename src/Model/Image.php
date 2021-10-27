<?php

namespace App\Model;

use PDO;

class Image
{
    private $db;

    public $id;

    public $uploadBy;

    public $originalName;

    public $path;

    public $filesize;

    public $uploadDir;

    public $inputNname;

    const MAX_FILE_SIZE = 4 * 1024 * 1024;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function makeDirectory($uploadDir)
    {
        if (!is_dir($uploadDir)) {
            return mkdir($uploadDir, 0777, true);
        }

        return true;
    }

    public function get(int $id)
    {
        $query = $this->db->prepare('
            SELECT *
            FROM images   
            WHERE id=:id
            ');

        $query->bindParam(':id', $id);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Upload an image
     *
     * @param $uploadDir
     * @param $inputName
     * @return array
     * @throws \Exception
     */
    function uploadImage($uploadDir, $inputName)
    {
        $this->makeDirectory($uploadDir);
        $image_file = $_FILES[$inputName];

        $image_extension = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);

        if (filesize($image_file['tmp_name']) > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'error' => 'Image size is too big'
            ];
        }

        $filename = bin2hex(random_bytes(20));
        $new_filename = $filename . '.' . $image_extension;

        $copied = move_uploaded_file($image_file['tmp_name'], $uploadDir . $new_filename);

        if ($copied) {
            $image_path = realpath($uploadDir . '/' . $new_filename);
            chmod($image_path, 0777);
            $relative_image_path = str_replace(ROOT_DIR, '', $image_path);
            $query = $this->db->prepare('
                INSERT INTO images(original_name, path, filesize, uploaded_by) 
                VALUES (:name, :path, :size, :author)
            ');

            $query->bindParam(1, $image_file['name']);
            $query->bindParam(2, $relative_image_path);
            $query->bindParam(3, $image_file['size']);
            $query->bindParam(4, $_SESSION['user']['id']);

            $exec = $query->execute([
                ':name' => $image_file['name'],
                ':path' => $relative_image_path,
                ':size' => $image_file['size'],
                ':author' => $_SESSION['user']['id'],
            ]);

            if ($exec) {
                return [
                    'success' => true,
                    'id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Could not add the image in database.'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Could not upload the image.'
        ];
    }

    public function deleteImage($id, $authorId)
    {
        $image = $this->get($id);

        if (!$image) {
            return true;
        }

        $query = $this->db->prepare('
            DELETE 
            FROM images 
            WHERE id = :id AND uploaded_by = :authorId'
        );

        $query->bindParam(':id', $id);
        $query->bindParam(':authorId', $authorId);
        $row_deleted = $query->execute();

        $image_path = ROOT_DIR . $image['path'];
        if ($row_deleted) {
            return unlink($image_path);
        } else {
            return false;
        }
    }

    /**
     * @throws \Exception
     */
    public function replaceImage($imageId, $userId, $uploadDir, $inputName)
    {
        $deleted = $this->deleteImage($imageId, $userId);

        if ($deleted) {
            return $this->uploadImage($uploadDir, $inputName);
        } else {
            return false;
        }
    }
}
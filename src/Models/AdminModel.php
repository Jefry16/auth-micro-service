<?php

namespace Models;

use PDO;
use Database;
use Model;

class AdminModel extends Model
{
    private PDO $conn;
    protected $errors = [];
    protected $allowedFields = ['name', 'active', 'password'];

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getById($id): array | bool
    {
        $sql = "SELECT * FROM admins WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data !== false) {
            $data['active'] = (bool) $data['active'];
        }

        return $data;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM admins ORDER BY name";

        $stmt = $this->conn->query($sql);

        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $row['active'] = (bool) $row['active'];

            $data[] = $row;
        };

        return $data;
    }

    public function create(array $data): int
    {

        $sql = "INSERT INTO admins (name,  active, password, email) VALUES (:name, :active, :password, :email)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':name', trim($data['name']), PDO::PARAM_STR);

        $stmt->bindValue(':active', isset($data['active']) ?? false, PDO::PARAM_BOOL);

        $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);

        $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function update(string $id, array $data): int
    {

        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sets = array_map(function ($value) {

            return $value . ' = :' . $value;
        }, array_keys($data));

        $sql = "UPDATE admins SET " . implode(', ', $sets) . " WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        foreach ($data as $key => $value) {
            //might need to set the PDO::PAMARAMS in the bind value
            $stmt->bindValue(':' . $key, trim($value));
        }

        $stmt->bindValue(':id', $id);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete($id): int
    {
        $sql = "DELETE FROM admins WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function login(string $email, string $password): array | bool
    {
        $sql = "SELECT email, password, id FROM admins WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data !== false && password_verify($password, $data['password'])) {

            return ['sub' => $data['id']];
        }

        return false;
    }
}

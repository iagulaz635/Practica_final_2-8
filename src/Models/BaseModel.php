<?php
namespace App\Models;

use App\Core\Database;

abstract class BaseModel
{
    public function __construct(
        protected Database $db,
        protected string $table
    ) {
    }

    public function getAll(): ?array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table}");
    }

    public function getById(int $id): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    public function delete(int $id): void
    {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);

        $fields       = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $this->db->query(
            "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)",
            $data
        );

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "$column = :$column";
        }

        $data['id'] = $id;

        $this->db->query(
            "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id",
            $data
        );
    }
}
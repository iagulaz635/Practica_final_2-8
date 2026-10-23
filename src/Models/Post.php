<?php
namespace App\Models;

use App\Core\Database;

class Post extends BaseModel
{
    public function __construct(Database $db)
    {
        parent::__construct($db, 'posts');
    }

    /**
     * Lista los posts con el nombre del autor.
     * Si hay búsqueda, filtra por título o categoría.
     */
    public function search(?string $term = null): ?array
    {
        $sql = 'SELECT p.*, u.username
                FROM posts p
                JOIN users u ON u.id = p.user_id';
        $params = [];

        if ($term !== null && $term !== '') {
            $sql .= ' WHERE p.title LIKE :term_title OR p.category LIKE :term_category';
            $params['term_title']    = "%$term%";
            $params['term_category'] = "%$term%";
        }

        $sql .= ' ORDER BY p.created_at DESC';

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Un post con el nombre de su autor.
     */
    public function findWithAuthor(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT p.*, u.username
             FROM posts p
             JOIN users u ON u.id = p.user_id
             WHERE p.id = :id',
            ['id' => $id]
        );
    }
}
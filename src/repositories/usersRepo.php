<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for users table
 */
class usersRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'users';
        $this->primaryKey = ['id'];
        $this->model = 'user';
        $this->autoload = true;
        $this->softDelete = true;
        parent::__construct();
    }

    /**
     * @param string|null $orderBy
     * @param string|null $where
     * @param string|null $groupBy
     * @return array
     */
    public function getAll(?string $orderBy = null, ?string $where = null, ?string $groupBy = null): array
    {
        $sql = "SELECT *
                FROM users";
        if ($where !== null) {
            $sql .= " WHERE $where";
        }
        if ($groupBy !== null) {
            $sql .= " GROUP BY $groupBy";
        }
        if ($orderBy !== null) {
            $sql .= " ORDER BY $orderBy";
        }
        return $this->getByQuery($sql);
    }
}
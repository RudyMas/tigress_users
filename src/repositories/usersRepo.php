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
        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS naam, CONCAT(last_name, ' ', first_name) AS fullname,
                email, 'OLSC Limburg' as platform
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

    /**
     * @param mixed $id
     * @return array
     */
    public function getById(mixed $id): array
    {
        $sql = "SELECT u.*, g.id as functie_funeva_id, g.functie as functie_funeva
                FROM users u
                LEFT OUTER JOIN mis_rechten_guna g
                    ON u.id = g.id_gebruiker AND g.tool = 'funeva'
                WHERE u.id = :id";
        $keyBindings = [
            ':id' => $id
        ];
        return $this->getByQuery($sql, $keyBindings);
    }

    /**
     * Get all users with access level 59
     *
     * @param int $id
     * @param int $school_id
     * @param string $text
     * @return string
     */
    public function getSelectOptiesOndersteunerById(int $id = 0, int $school_id = 0, string $text = 'Maak je keuze'): string
    {
        $scholen = new scholenRepo();
        if ($school_id <> 0) {
            $scholen->loadById($school_id);
            $school = $scholen->current();
        }

        if (in_array($_SESSION['user']['access_level'], [59, 79]) && !$scholen->isEmpty()) {
            $sql = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as naam
                FROM users u
                WHERE u.access_level = 59
                  AND u.team_id = :team_id
                ORDER BY u.first_name, u.last_name";
            $keyBindings = [
                ':team_id' => $school->team_id
            ];
        } else {
            $sql = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as naam
                FROM users u
                WHERE u.access_level = 59
                ORDER BY u.first_name, u.last_name";
            $keyBindings = [];
        }
        $users = $this->getByQuery($sql, $keyBindings);

        return $this->createOptionsByData($id, $text, 'naam', 'id', $users);
    }
}
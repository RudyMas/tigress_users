<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for users
 */
class UsersRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'users';
        $this->primaryKey = ['id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        $this->softDelete = true;
        $this->createTable = [
            'table' => "
                CREATE TABLE {$this->table} (
                  `id` int(11) NOT NULL,
                  `oauth_provider` enum('local','facebook','google','itsme','linkedin','microsoft','smartschool') NOT NULL,
                  `oauth_uid` varchar(100) DEFAULT NULL,
                  `username` varchar(50) DEFAULT NULL,
                  `first_name` varchar(30) NOT NULL,
                  `last_name` varchar(30) NOT NULL,
                  `email` varchar(100) DEFAULT NULL,
                  `gender` enum('f','m','x','?') NOT NULL DEFAULT '?',
                  `locale` varchar(6) NOT NULL,
                  `avatar` tinytext DEFAULT NULL,
                  `salt` varchar(64) NOT NULL,
                  `authorized` varchar(64) NOT NULL,
                  `access_level` int(11) NOT NULL,
                  `last_login` timestamp NOT NULL,
                  `created` timestamp NOT NULL,
                  `created_user_id` int(11) NOT NULL,
                  `modified` timestamp NOT NULL,
                  `modified_user_id` int(11) NOT NULL,
                  `deleted` timestamp NOT NULL,
                  `deleted_user_id` int(11) NOT NULL,
                  `active` int(11) NOT NULL DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
                ",
            'indexes' => [
                "ALTER TABLE {$this->table} ADD PRIMARY KEY (`id`);",
                "ALTER TABLE {$this->table} MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;"
            ],
            'seed' => [
                "INSERT INTO {$this->table} (`id`, `oauth_provider`, `oauth_uid`, `username`, `first_name`, `last_name`, `email`, `gender`, `locale`, `avatar`, `salt`, `authorized`, `access_level`, `last_login`, `created`, `created_user_id`, `modified`, `modified_user_id`, `deleted`, `deleted_user_id`, `active`) VALUES
                   (1, 'local', '', 'super.admin', 'Super', 'Administrator', '', '?', 'en-US', '', '466a0df987e370c19497f3e3a5bb549cb72db92b696107ec7af561c2c04ddaf1', '91459b40c2427ccee86c710bd09d9a6446a5ad608cc3ff4407bfb838d7352c99', 100, '2026-01-01 00:00:00', '2026-01-01 00:00:00', 1, '2026-01-01 00:00:00', 1, '2026-01-01 00:00:00', 1, 1);"
            ]
        ];
        parent::__construct();
    }

    /**
     * Get all users with optional ordering, filtering, and grouping
     *
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

    /**
     * Get the names of workers
     *
     * @param string $worker_ids
     * @return string
     */
    public function getNames(string $worker_ids): string
    {
        $worker_ids = json_decode($worker_ids, true);
        if (empty($worker_ids)) {
            return __('No employee assigned');
        }

        $this->reset();
        $this->loadByWhereQuery('id IN (' . implode(',', $worker_ids) . ')', [], 'first_name, last_name');

        $tekst = '';
        foreach ($this as $row) {
            if ($tekst !== '') {
                $tekst .= ', ';
            }
            $tekst .= "{$row->first_name} {$row->last_name}";
        }
        return $tekst;
    }

    /**
     * Create select options for users
     *
     * @param array|null $user_ids
     * @return string
     */
    public function getSelectOptions(?array $user_ids): string
    {
        $this->reset();
        $this->loadAllActive('first_name, last_name');

        $options = '';
        foreach ($this as $row) {
            $selected = (!is_null($user_ids) && in_array($row->id, $user_ids)) ? ' selected' : '';
            $options .= "<option value='{$row->id}'{$selected}>{$row->id}. {$row->first_name} {$row->last_name}</option>";
        }

        return $options;
    }

    /**
     * Create select options for workers
     *
     * @param array|null $worker_ids
     * @param array|null $project_team_member_ids
     * @return string
     */
    public function getSelectOptionsWorkers(?array $worker_ids, ?array $project_team_member_ids): string
    {
        if (is_null($project_team_member_ids)) {
            return '<option value="0" disabled>' . __('No employees linked to project') . '</option>';
        }

        $this->reset();
        $this->loadByWhereQuery('id IN (' . implode(',', $project_team_member_ids) . ')', [], 'first_name, last_name');

        $options = '';
        foreach ($this as $row) {
            $selected = (!is_null($worker_ids) && in_array($row->id, $worker_ids)) ? ' selected' : '';
            $options .= "<option value='{$row->id}'{$selected}>{$row->first_name} {$row->last_name}</option>";
        }

        return $options;
    }
}
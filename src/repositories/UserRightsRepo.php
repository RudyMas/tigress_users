<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for user rights table
 */
class UserRightsRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'users_rights';
        $this->primaryKey = ['id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        $this->softDelete = true;
        $this->createTable = [
            'table' => "
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    `id` int(11) NOT NULL,
                    `name` varchar(25) NOT NULL,
                    `active` tinyint(1) NOT NULL DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;",
            'indexes' => [
                "ALTER TABLE {$this->table} ADD UNIQUE KEY `id` (`id`);"
            ],
            'seed' => [
                "INSERT INTO `users_rights` (`id`, `name`, `active`) VALUES
                                            (-1, 'No Access', 1),
                                            (5, 'New User', 1),
                                            (10, 'User', 1),
                                            (99, 'Administrator', 1),
                                            (100, 'Super Administrator', 1);"
            ]
        ];
        parent::__construct();
    }
}
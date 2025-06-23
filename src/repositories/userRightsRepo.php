<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for user rights table
 */
class userRightsRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'users_rights';
        $this->primaryKey = ['id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        $this->softDelete = true;
        parent::__construct();
    }

    /**
     * Get options for select
     *
     * @param int $right_id
     * @param string $text
     * @param string $display
     * @param string $value
     * @return string
     */
    public function getSelectOptions(
        int $right_id,
        string $text = 'Select access level',
        string $display = 'name',
        string $value = 'id'
    ): string
    {
        $this->loadAll('id');
        return $this->createOptions($right_id, $text, $display, $value);
    }
}
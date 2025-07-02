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
        TRANSLATIONS->load(SYSTEM_ROOT . '/vendor/tigress/users/translations/translations.json');

        $this->dbName = 'default';
        $this->table = 'users';
        $this->primaryKey = ['id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        $this->softDelete = true;
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
            return '<option value="0" disabled>' . __('No employees linked to project', $this->translationFile) . '</option>';
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
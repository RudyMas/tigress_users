<?php

namespace Controller\users;

use JetBrains\PhpStorm\NoReturn;
use Repository\systemRightsRepo;
use Repository\userRightsRepo;
use Repository\usersRepo;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersCrudController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.03.13.0
 * @package Tigress\Users
 */
class UsersCrudController
{
    /**
     * Get all active users
     *
     * @param array $args
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getUsers(array $args): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to view the users page.";
            TWIG->redirect('/login');
        }

        $active = ($args['show'] == 'active') ? 1 : 0;

        $users = new usersRepo();

        if ($_SESSION['user']['access_level'] < 100) {
            $usersData = $users->getAll(null, "active = {$active} AND access_level < 100");
        } else {
            $usersData = $users->getAll(null , "active = {$active}");
        }

        $userRights = new userRightsRepo();
        $userRights->loadAll('id');

        foreach ($usersData as &$user) {
            $user->access_level_name = $userRights->get($user->access_level)->name;
        }

        TWIG->render(null, $usersData, 'DT');
    }

    /**
     * Save user
     *
     * @return void
     */
    #[NoReturn] public function saveUser(): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to perform this action.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->loadById($_POST['id']);
        $user = $users->current();
        $user->updateFromPost($_POST);
        $users->save($user);

        if (isset($_POST['save_default'])) {
            $systemRights = new systemRightsRepo();
            $systemRights->updateRightsUser('home/tiles.json', $_POST['id'], $_POST['access_level']);
        }

        $_SESSION['success'] = "User successful saved.";
        TWIG->redirect('/users');
    }

    /**
     * Save user rights
     *
     * @param array $args
     * @return void
     */
    #[NoReturn] public function saveUserRights(array $args): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to perform this action.";
            TWIG->redirect('/login');
        }

        $systemRights = new systemRightsRepo();
        $systemRights->deleteByPrimaryKey([
            'user_id' => $_POST['id'],
        ]);

        if (isset($_POST['rights'])) {
            foreach ($_POST['rights'] as $tool => $data) {
                $access = $data['access'] ?? 0;
                $read = $data['read'] ?? 0;
                $write = $data['write'] ?? 0;
                $delete = $data['delete'] ?? 0;

                $systemRights->new();
                $systemRight = $systemRights->current();
                $systemRight->user_id = (int)$_POST['id'];
                $systemRight->tool = $tool;
                $systemRight->access = (int)$access;
                $systemRight->read = (int)$read;
                $systemRight->write = (int)$write;
                $systemRight->delete = (int)$delete;
                $systemRights->updateCurrent($systemRight);
            }
            $systemRights->saveAll();
        }

        $_SESSION['success'] = "Rights successfully saved.";
        TWIG->redirect('/users');
    }

    /**
     * Delete user
     *
     * @return void
     */
    #[NoReturn] public function deleteUser(): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to delete a user.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->deleteById($_POST['DeleteUser']);

        $_SESSION['success'] = "User successfully archived.";
        TWIG->redirect('/users');
    }

    /**
     * Undelete user
     *
     * @return void
     */
    #[NoReturn] public function undeleteUser(): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to restore a user.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->undeleteById((int)$_POST['RestoreUser']);

        $_SESSION['success'] = "User successfully restored.";
        TWIG->redirect('/users');
    }
}
<?php

namespace Controller\users;

use JetBrains\PhpStorm\NoReturn;
use Repository\gunaRechtenRepo;
use Repository\linkUsersScholenRepo;
use Repository\systemRightsRepo;
use Repository\userRightsRepo;
use Repository\usersRepo;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2024-2025 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2025.02.10.0
 * @package Controller\olsc
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
            $_SESSION['error'] = "U heeft niet de juiste rechten om de users pagina te bekijken.";
            TWIG->redirect('/login');
        }

        $active = ($args['toon'] == 'active') ? 1 : 0;

        $users = new usersRepo();

        $sql = "SELECT u.id as id, u.first_name, u.last_name, u.modified, u.email, 
                        GROUP_CONCAT(DISTINCT s.school ORDER BY s.school SEPARATOR ', ') as school, 
                        t.team, u.access_level, g.functie
                FROM users u
                    LEFT JOIN link_users_scholen l
                        ON l.user_id = u.id
                    LEFT JOIN scholen s
                        ON s.id = l.school_id
                    LEFT OUTER JOIN teams t
                        ON t.id = u.team_id
                    LEFT OUTER JOIN mis_rechten_guna g
                        ON u.id = g.id_gebruiker AND g.tool = 'funeva'
                WHERE u.active = {$active}";
        if ($_SESSION['user']['access_level'] < 100) {
            $sql .= " AND u.access_level < 100";
        }
        $sql .= " GROUP BY u.id, u.first_name, u.last_name, u.modified, u.email, t.team, u.access_level, g.functie";

        $usersArray = $users->getByQuery($sql);

        $userRights = new userRightsRepo();
        $userRights->loadAll('id');

        foreach ($usersArray as &$user) {
            $user->access_level_name = $userRights->get($user->access_level)->name;
        }

        TWIG->render(null, $usersArray, 'DT');
    }

    /**
     * Save user
     *
     * @return void
     */
    #[NoReturn] public function saveUser(): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "U heeft niet de juiste rechten om een gebruiker op te slaan.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->loadById($_POST['id']);
        $user = $users->current();
        $user->updateFromPost($_POST);
        $users->save($user);

        $linkUsersScholen = new linkUsersScholenRepo();
        $sql = "DELETE FROM link_users_scholen WHERE user_id = :user_id";
        $keyBindings = [
            ':user_id' => $_POST['id']
        ];
        $linkUsersScholen->deleteByQuery($sql, $keyBindings);

        if (isset($_POST['link_user_school']) && is_array($_POST['link_user_school'])) {
            foreach ($_POST['link_user_school'] as $school_id) {
                $linkUsersScholen->new();
                $linkUserSchool = $linkUsersScholen->current();
                $linkUserSchool->user_id = (int)$_POST['id'];
                $linkUserSchool->school_id = (int)$school_id;
            }
            $linkUsersScholen->saveAll();
        }

        if (isset($_POST['save_default'])) {
            $systemRights = new systemRightsRepo();
            $systemRights->updateRightsUser('home/tiles.json', $_POST['id'], $_POST['access_level']);
        }

        $_SESSION['success'] = "Gebruiker succesvol opgeslagen.";
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
            $_SESSION['error'] = "U heeft niet de juiste rechten om een gebruiker zijn rechten aan te passen.";
            TWIG->redirect('/login');
        }

        $systemRights = new systemRightsRepo();
        $systemRights->deleteByPrimaryKey([
            'user_id' => $_POST['id'],
        ]);

        if (isset($_POST['rechten'])) {
            foreach ($_POST['rechten'] as $tool => $data) {
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

        $gunaRechten = new gunaRechtenRepo();

        if (!empty($_POST['functie_funeva_id'])) {
            if ($_POST['functie_funeva'] == 0) {
                $gunaRechten->deleteById($_POST['functie_funeva_id']);
            } else {
                $gunaRechten->loadById($_POST['functie_funeva_id']);
                $gunaRecht = $gunaRechten->current();
                $gunaRecht->functie = $_POST['functie_funeva'];
                $gunaRechten->save($systemRight);
            }
        } else {
            if ($_POST['functie_funeva'] == 0) {
                TWIG->redirect('/users');
            }
            $gunaRechten->new();
            $gunaRecht = $gunaRechten->current();

            $gunaRecht->tool = 'funeva';
            $gunaRecht->id_instelling = '1';
            $gunaRecht->id_gebruiker = (int)$_POST['id'];
            $gunaRecht->functie = (int)$_POST['functie_funeva'];
            $gunaRechten->save($gunaRecht);
        }

        $_SESSION['success'] = "Rechten succesvol opgeslagen.";
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
            $_SESSION['error'] = "U heeft niet de juiste rechten om een gebruiker te verwijderen.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->deleteById($_POST['VerwijderUser']);

        $_SESSION['success'] = "Gebruiker succesvol verwijderd.";
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
            $_SESSION['error'] = "U heeft niet de juiste rechten om een gebruiker te herstellen.";
            TWIG->redirect('/login');
        }

        $users = new usersRepo();
        $users->undeleteById((int)$_POST['TerugzettenUser']);

        $_SESSION['success'] = "Gebruiker succesvol hersteld.";
        TWIG->redirect('/users');
    }
}
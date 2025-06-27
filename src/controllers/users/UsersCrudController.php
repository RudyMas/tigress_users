<?php

namespace Controller\users;

use JetBrains\PhpStorm\NoReturn;
use Repository\SystemRightsRepo;
use Repository\UserRightsRepo;
use Repository\UsersRepo;
use Tigress\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersCrudController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.06.27.0
 * @package Tigress\Users
 */
class UsersCrudController extends Controller
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
            TWIG->render(null, [], 'DT');
            exit;
        }

        $active = ($args['show'] == 'active') ? 1 : 0;

        $users = new UsersRepo();

        if ($_SESSION['user']['access_level'] < 100) {
            $usersData = $users->getAll(null, "active = {$active} AND access_level < 100");
        } else {
            $usersData = $users->getAll(null , "active = {$active}");
        }

        $userRights = new UserRightsRepo();
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
        $this->checkRights('write');

        $users = new UsersRepo();
        $users->loadById($_POST['id']);
        $user = $users->current();
        $user->updateFromPost($_POST);
        $users->save($user);

        if (isset($_POST['save_default'])) {
            $systemRights = new SystemRightsRepo();
            $systemRights->updateRightsUser('home/tiles.json', $_POST['id'], $_POST['access_level']);
        }

        $_SESSION['success'] = match (substr(CONFIG->website->html_lang, 0, 2)) {
            'nl' => "Gebruiker succesvol opgeslagen.",
            'fr' => "Utilisateur enregistré avec succès.",
            'de' => "Benutzer erfolgreich gespeichert.",
            'es' => "Usuario guardado con éxito.",
            'it' => "Utente salvato con successo.",
            'sv' => "Korisnik uspješno spremljen.",
            default => "User successfully saved."
        };
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
        $this->checkRights('write');

        $systemRights = new SystemRightsRepo();
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

        $_SESSION['success'] = match (substr(CONFIG->website->html_lang, 0, 2)) {
            'nl' => "Rechten succesvol opgeslagen.",
            'fr' => "Droits enregistrés avec succès.",
            'de' => "Rechte erfolgreich gespeichert.",
            'es' => "Derechos guardados con éxito.",
            'it' => "Diritti salvati con successo.",
            'sv' => "Rättigheter sparade.",
            default => "Rights successfully saved."
        };
        TWIG->redirect('/users');
    }

    /**
     * Delete user
     *
     * @return void
     */
    #[NoReturn] public function deleteUser(): void
    {
        $this->checkRights('delete');

        $users = new UsersRepo();
        $users->deleteById($_POST['DeleteUser']);

        $_SESSION['success'] = match (substr(CONFIG->website->html_lang, 0, 2)) {
            'nl' => "Gebruiker succesvol gearchiveerd.",
            'fr' => "Utilisateur archivé avec succès.",
            'de' => "Benutzer erfolgreich archiviert.",
            'es' => "Usuario archivado con éxito.",
            'it' => "Utente archiviato con successo.",
            'sv' => "Användare arkiverad.",
            default => "User successfully archived."
        };
        TWIG->redirect('/users');
    }

    /**
     * Undelete user
     *
     * @return void
     */
    #[NoReturn] public function undeleteUser(): void
    {
        $this->checkRights('delete');

        $users = new UsersRepo();
        $users->undeleteById((int)$_POST['RestoreUser']);

        $_SESSION['success'] = match (substr(CONFIG->website->html_lang, 0, 2)) {
            'nl' => "Gebruiker succesvol hersteld.",
            'fr' => "Utilisateur restauré avec succès.",
            'de' => "Benutzer erfolgreich wiederhergestellt.",
            'es' => "Usuario restaurado con éxito.",
            'it' => "Utente ripristinato con successo.",
            'sv' => "Användare återställd.",
            default => "User successfully restored."
        };
        TWIG->redirect('/users');
    }
}
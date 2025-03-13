<?php

namespace Controller\users;

use Repository\systemRightsRepo;
use Repository\userRightsRepo;
use Repository\usersRepo;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.03.13.0
 * @package Tigress\Users
 */
class UsersController
{
    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        TWIG->addPath('vendor/tigress/users/src/views');
    }

    /**
     * Homepage of the website
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function index(): void
    {
        if (RIGHTS->checkRights('read') === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to view the users page.";
            TWIG->redirect('/login');
        }

        TWIG->render('users/index.twig', [
        ]);
    }

    /**
     * Edit user
     *
     * @param array $args
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function editUser(array $args): void
    {
        if (RIGHTS->checkRights('read') === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to view the users page.";
            TWIG->redirect('/login');
        }

        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $users = new usersRepo();
        $users->loadById($args['id']);

        if ($users->isEmpty()) {
            $_SESSION['error'] = "We couldn't find the user's information.";
            TWIG->redirect('/users');
        }

        TWIG->render('users/edit.twig', [
            'user' => $users->current(),
            'selectOptiesRights' => new userRightsRepo()->getSelectOptions($users->current()->access_level, false),
        ]);
    }

    /**
     * Edit user rights
     *
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function editUserRights(array $args): void
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "You do not have the appropriate permissions to view the users rights page.";
            TWIG->redirect('/login');
        }

        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $users = new usersRepo();
        $users->loadById($args['id']);
        $user = $users->current();

        $systemRights = new systemRightsRepo();
        $userRights = $systemRights->getRightsByUserId($args['id']);

        $security = $systemRights->createSecurityMatrix('home/tiles.json');

        $rightsMatrix = [];
        foreach ($security as $key => $value) {
            if (!strpos(json_encode($value), 'special_rights')) continue;
            foreach ($value as $keySub => $valueSub) {
                if (!isset($valueSub['special_rights'])) continue;
                $rightsMatrix[$key][$keySub]['special_rights'] = $valueSub['special_rights'];
                $rightsMatrix[$key][$keySub]['access'] = $userRights[$valueSub['special_rights']]['access'] ?? 0;
                $rightsMatrix[$key][$keySub]['read'] = $userRights[$valueSub['special_rights']]['read'] ?? 0;
                $rightsMatrix[$key][$keySub]['write'] = $userRights[$valueSub['special_rights']]['write'] ?? 0;
                $rightsMatrix[$key][$keySub]['delete'] = $userRights[$valueSub['special_rights']]['delete'] ?? 0;
                $rightsMatrix[$key][$keySub]['all'] = in_array($user->access_level, $valueSub['level_rights']) || $user->access_level == 100;
            }
        }

        TWIG->render('users/edit_rights.twig', [
            'user' => $user,
            'rightsMatrix' => $rightsMatrix,
        ]);
    }
}
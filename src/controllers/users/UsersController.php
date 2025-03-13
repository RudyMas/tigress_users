<?php

namespace Controller\users;

use Repository\linkUsersScholenRepo;
use Repository\systemRightsRepo;
use Repository\teamsRepo;
use Repository\userRightsRepo;
use Repository\usersRepo;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2024 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2024.12.13.0
 * @package Controller\olsc
 */
class UsersController
{
    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        TWIG->addPath('vendor/olsc/users/src/views');
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
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "U heeft niet de juiste rechten om de users pagina te bekijken.";
            TWIG->redirect('/login');
        }

        TWIG->render('users/index.twig', [
            'filterOptiesTeams' => new teamsRepo()->getSelectOptions(0, 'Allemaal', 'team', 'team'),
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
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = "U heeft niet de juiste rechten om de users pagina te bekijken.";
            TWIG->redirect('/login');
        }

        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $users = new usersRepo();
        $users->loadById($args['id']);

        if ($users->isEmpty()) {
            $_SESSION['error'] = "Gebruiker niet gevonden.";
            TWIG->redirect('/users');
        }

        TWIG->render('users/edit.twig', [
            'user' => $users->current()->getProperties(),
            'selectOptiesRechten' => new userRightsRepo()->getSelectOptions($users->current()->access_level, false),
            'selectOptiesUsersScholen' => new linkUsersScholenRepo()->getSelectOptiesUsersScholenByUserId($users->current()->id),
            'selectOptiesTeams' => new teamsRepo()->getSelectOptions($users->current()->team_id),
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
            $_SESSION['error'] = "U heeft niet de juiste rechten om de users rechten pagina te bekijken.";
            TWIG->redirect('/login');
        }

        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $users = new usersRepo();
        $user = $users->getById($args['id'])[0];

        $systemRights = new systemRightsRepo();
        $rechten = $systemRights->getRightsByUserId($args['id']);

        $security = $systemRights->createSecurityMatrix('home/tiles.json');

        foreach ($security as $key => $value) {
            if (!strpos(json_encode($value), 'special_rights')) continue;
            foreach ($value as $keySub => $valueSub) {
                if (!isset($valueSub['special_rights'])) continue;
                $rights[$key][$keySub]['special_rights'] = $valueSub['special_rights'];
                $rights[$key][$keySub]['access'] = $rechten[$valueSub['special_rights']]['access'] ?? 0;
                $rights[$key][$keySub]['read'] = $rechten[$valueSub['special_rights']]['read'] ?? 0;
                $rights[$key][$keySub]['write'] = $rechten[$valueSub['special_rights']]['write'] ?? 0;
                $rights[$key][$keySub]['delete'] = $rechten[$valueSub['special_rights']]['delete'] ?? 0;
                $rights[$key][$keySub]['all'] = in_array($user->access_level, $valueSub['level_rights']) || $user->access_level == 100;
            }
        }

        TWIG->render('users/rechten.twig', [
            'user' => $user,
            'rechten' => $rights,
            'optionsFunctieFunEva' => [
                0 => 'Gebruiker',
                1 => 'Coach',
                2 => 'Evaluator',
                3 => 'Coach & Evaluator',
                4 => 'Directie',
                5 => 'Personeelsdienst',
            ],
        ]);
    }
}
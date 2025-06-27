<?php

namespace Controller\users;

use Repository\SystemRightsRepo;
use Repository\UserRightsRepo;
use Repository\UsersRepo;
use Tigress\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class UsersController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.06.27.0
 * @package Tigress\Users
 */
class UsersController extends Controller
{
    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        TWIG->addPath('vendor/tigress/users/src/views');
        TWIG->addGlobal('translations', json_decode(file_get_contents(SYSTEM_ROOT . '/vendor/tigress/users/translations/translations.json'), true));
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
        $this->checkRights('read');
        TWIG->render('users/index.twig');
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
        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $this->checkRights('edit');

        $users = new UsersRepo();
        $users->loadById($args['id']);

        if ($users->isEmpty()) {
            $_SESSION['error'] = match (substr(CONFIG->website->html_lang, 0, 2)) {
                'nl' => "We konden de gebruikersinformatie niet vinden.",
                'fr' => "Nous n'avons pas pu trouver les informations de l'utilisateur.",
                'de' => "Wir konnten die Benutzerinformationen nicht finden.",
                'es' => "No pudimos encontrar la información del usuario.",
                'it' => "Non siamo riusciti a trovare le informazioni dell'utente.",
                'sv' => "Vi kunde inte hitta användarens information.",
                default => "We couldn't find the user's information."
            };
            TWIG->redirect('/users');
        }

        TWIG->render('users/edit.twig', [
            'user' => $users->current(),
            'selectOptiesRights' => new UserRightsRepo()->getSelectOptions($users->current()->access_level, false),
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
        SECURITY->checkAccess();
        SECURITY->checkReferer(['/users']);

        $this->checkRights('edit');

        $users = new UsersRepo();
        $users->loadById($args['id']);
        $user = $users->current();

        $systemRights = new SystemRightsRepo();
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
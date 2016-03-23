<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;

class MainMenu extends MenuItem
{
    /**
     * MainMenu constructor.
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param FactoryInterface $factory
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, FactoryInterface $factory)
    {
        parent::__construct('root', $factory);

        try {
            if($authorizationChecker->isGranted('ROLE_BACKEND_ACCESS')) {
                $this->addChild('admin_form', [
                    'label' => 'app.menu.forms',
                    'route' => 'admin_get_forms',
                ]);
                $this->addChild('admin_documentation', [
                    'label' => 'app.menu.documentation',
                    'route' => 'admin_get_documentations',
                ]);
            }
        } catch(AuthenticationCredentialsNotFoundException $ex) {
            // Thrown when there is no token (on error pages)
        }

    }

}
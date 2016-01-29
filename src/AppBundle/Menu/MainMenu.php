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
            if($authorizationChecker->isGranted('ROLE_ADMIN')) {
            }
        } catch(AuthenticationCredentialsNotFoundException $ex) {
            // Thrown when there is no token (on error pages)
        }

    }

}
<?php

namespace AppBundle\Security;

use AppBundle\Entity\Form;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleInterface;

class FormAccessVoter extends Voter
{
    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['EDIT', 'LIST_ENROLLMENTS', 'EDIT_ENROLLMENT']) && $subject instanceof Form;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /* @var $subject Form */
        if($attribute === 'EDIT' && $subject->getCreatedBy() === $token->getUsername())
            return true;
        $availableRoles = array_map(function (RoleInterface $role) {
            return $role->getRole();
        }, $token->getRoles());

        $requiredRoles = [];
        switch($attribute) {
            case 'EDIT':
                $requiredRoles = $subject->getEditFormGroups();
                break;
            case 'LIST_ENROLLMENTS':
                $requiredRoles = $subject->getListEnrollmentsGroups();
                break;
            case 'EDIT_ENROLLMENT':
                $requiredRoles = $subject->getEditEnrollmentsGroups();
                break;
        }

        $requiredRoles = array_map(function($groupName) {
            return 'ROLE_GROUP_'.strtoupper($groupName);
        }, $requiredRoles);
        return count(array_intersect($requiredRoles, $availableRoles)) > 0;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use vierbergenlars\Bundle\AuthClientBundle\Entity\User as AUser;


/**
 * @ORM\Entity
 * @ORM\Table(name="app_user")
 */
class User extends AUser
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $realname;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * Gets the roles the user is assigned. They are based on the groups the user is member of.
     *
     * Each group name gets converted to upper case, and ROLE_GROUP_ gets prepended.
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = parent::getRoles();
        $roles[] = 'ROLE_USER';
        return $roles;
    }
}

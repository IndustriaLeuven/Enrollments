<?php

namespace PluginBundle\Entity;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Doctrine\ORM\Mapping as ORM;

/**
 * ShortUrl
 *
 * @ORM\Table(name="plugin_short_url")
 * @ORM\Entity
 */
class ShortUrl
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", unique=true)
     */
    private $slug;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Form")
     */
    private $form;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Shorturl
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return Shorturl
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }
}


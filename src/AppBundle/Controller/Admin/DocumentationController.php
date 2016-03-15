<?php

namespace AppBundle\Controller\Admin;

use FOS\RestBundle\Controller\Annotations\NoRoute;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DocumentationController extends Controller implements ClassResourceInterface
{
    const DOC_DIR = '/../../../../documentation/plugins/';

    /**
     * @View
     */
    public function cgetAction()
    {
        return $this->sidebarAction();
    }

    /**
     * @View
     * @NoRoute()
     */
    public function sidebarAction()
    {
        return Finder::create()
            ->files()
            ->in(__DIR__.self::DOC_DIR);
    }

    /**
     * @View
     */
    public function getAction($doc)
    {
        foreach($this->sidebarAction() as $item)
            /* @var $item SplFileInfo */
            if($item->getRelativePathname() === $doc)
                return $item->getContents();
        throw $this->createNotFoundException();
    }
}

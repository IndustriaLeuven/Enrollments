<?php

namespace PluginBundle\Controller;

use AppBundle\Entity\Enrollment;
use AppBundle\Util;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use PluginBundle\Entity\ShortUrl;
use PluginBundle\Event\AdmissionCheckEvent;
use PluginBundle\EventListener\AdmissionCheckPluginListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShortUrlPluginController extends Controller
{
    /**
     * @Get("/f/{slug}")
     * @ParamConverter("shortUrl", options={"mapping": {"slug":"slug"}})
     */
    public function gotoAction(ShortUrl $shortUrl)
    {
        return $this->redirectToRoute('app_get_enrollment', [
            'form' => $shortUrl->getForm()->getId(),
        ]);
    }
}

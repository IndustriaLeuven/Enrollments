<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UI\FormTemplateEvent;
use AppBundle\Event\UIEvents;
use FOS\RestBundle\Controller\Annotations\NoRoute;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @View
 */
class EnrollmentController extends BaseController implements ClassResourceInterface
{
    /**
     * @View("AppBundle:Admin/Enrollment:sidebar.html.twig")
     * @NoRoute
     */
    public function sidebarAction(Form $form, $enrollment = null)
    {
        return $this->getEventDispatcher()
            ->dispatch(AdminEvents::ENROLLMENT_SIDEBAR, new EnrollmentSidebarEvent($form, $enrollment));
    }

    public function cgetAction(Request $request, Form $form)
    {
        $event = new EnrollmentListEvent($form, $request->query);
        $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_LIST, $event);

        $filteredData = $event->getForm()->getEnrollments()->matching($event->getCriteria());

        return [
            'data' => $this->paginate($filteredData, $request),
            'event' => $event,
        ];

    }

    public function getAction(Form $form, Enrollment $enrollment)
    {
        return $this->getEventDispatcher()->dispatch(UIEvents::SUCCESS, new EnrollmentTemplateEvent($form, $enrollment));
    }

    public function editAction(Form $form, Enrollment $enrollment)
    {
        throw new HttpException(501, 'Enrollment edit not implemented.');

    }

    public function removeAction(Form $form, Enrollment $enrollment)
    {
        throw new HttpException(501, 'Enrollment deletion not implemented.');
    }
}

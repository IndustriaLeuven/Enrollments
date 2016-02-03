<?php

namespace AppBundle\EventListener;

use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AdminEnrollmentDefaultsListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * AdminEnrollmentDefaultsListener constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::ENROLLMENT_SIDEBAR => 'onAdminEnrollmentSidebar',
            AdminEvents::ENROLLMENT_LIST => 'onAdminEnrollmentList',
        ];
    }

    public function onAdminEnrollmentSidebar(EnrollmentSidebarEvent $event)
    {
        $event->addTemplate(new TemplateReference('AppBundle', 'Admin/Enrollment/sidebar', 'navigation', 'html', 'twig'), [
            '_route' => $this->requestStack->getParentRequest()->attributes->get('_route'),
        ]);
    }

    public function onAdminEnrollmentList(EnrollmentListEvent $event)
    {
        $event->setField('_.data', 'Data', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'data', 'html', 'twig'));
        $event->setField('_.timestamp', 'Timestamp', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'timestamp', 'html', 'twig'));
        $event->getCriteria()->orderBy(['timestamp' => 'ASC']);
    }

}

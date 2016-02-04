<?php

namespace AppBundle\EventListener;

use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            AdminEvents::ENROLLMENT_GET => 'onAdminEnrollmentGet',
            AdminEvents::ENROLLMENT_EDIT => ['onAdminEnrollmentEdit', -255],
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
        $event->setField(['html'], '_.data', 'Data', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'data', 'html', 'twig'));
        $event->setField(EnrollmentListEvent::ALL_TYPES, '_.timestamp', 'Timestamp', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'timestamp', 'html', 'twig'));
        $event->getCriteria()->orderBy(['timestamp' => 'ASC']);
    }

    public function onAdminEnrollmentGet(EnrollmentTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(UIEvents::SUCCESS, $event);
    }

    public function onAdminEnrollmentEdit(EnrollmentEditEvent $event)
    {
        $formBuilder = $event->getFormBuilder();
        if($formBuilder->count()) {
            $formBuilder->add('submit', SubmitType::class);
        } else {
            $formBuilder->add('no_settings', FormStaticControlType::class, [
                'data' => 'There are no plugin settings to edit',
            ]);
        }
    }

}

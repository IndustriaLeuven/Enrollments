<?php

namespace AppBundle\EventListener;

use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentEvent;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $em;

    /**
     * AdminEnrollmentDefaultsListener constructor.
     * @param RequestStack $requestStack
     * @param EntityManager $em
     */
    public function __construct(RequestStack $requestStack, EntityManager $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::ENROLLMENT_SIDEBAR => 'onAdminEnrollmentSidebar',
            AdminEvents::ENROLLMENT_LIST => 'onAdminEnrollmentList',
            AdminEvents::ENROLLMENT_GET => 'onAdminEnrollmentGet',
            AdminEvents::ENROLLMENT_EDIT => ['onAdminEnrollmentEdit', -255],
            AdminEvents::ENROLLMENT_DELETE => 'onAdminEnrollmentDelete',
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
        $event->setTemplatingField(['html'], '_.data', 'Data', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'data', 'html', 'twig'));
        $event->setTemplatingField(EnrollmentListEvent::ALL_TYPES, '_.createdAt', 'Created at', new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'timestamp', 'html', 'twig'));
        $event->getCriteria()->orderBy(['createdAt' => 'ASC']);
    }

    public function onAdminEnrollmentGet(EnrollmentTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->dispatch(UIEvents::SUCCESS, $event);
    }

    public function onAdminEnrollmentEdit(EnrollmentEditEvent $event)
    {
        $formBuilder = $event->getFormBuilder();
        if($formBuilder->count()) {
            $formBuilder->add('submit', SubmitType::class, [
                'label' => 'form.submit',
            ]);
        } else {
            $formBuilder->add('no_settings', FormStaticControlType::class, [
                'label' => false,
                'data' => 'admin.enrollment.no_settings',
            ]);
        }
    }

    public function onAdminEnrollmentDelete(EnrollmentEvent $event)
    {
        $this->em->remove($event->getEnrollment());
    }

}

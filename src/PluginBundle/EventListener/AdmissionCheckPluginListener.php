<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentEditSubmitEvent;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use AppBundle\Plugin\Table\CallbackTableColumnDefinition;
use AppBundle\Util;
use PluginBundle\Event\AdmissionCheckEvent;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AdmissionCheckPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'admission_check';

    use PluginConfigurationHelperTrait;
    use EnrollmentEditHelperTrait;

    /**
     * Secret for signing QR-codes
     * @var string
     */
    private $secret;

    /**
     * EntranceCheckPluginListener constructor.
     *
     * @param string $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            AdminEvents::ENROLLMENT_LIST => 'onAdminEnrollmentList',
            AdminEvents::ENROLLMENT_GET => 'onAdminEnrollmentGet',
            AdminEvents::ENROLLMENT_EDIT => 'onAdminEnrollmentEdit',
            AdminEvents::ENROLLMENT_EDIT_SUBMIT => 'onAdminEnrollmentEditSubmit',
            UIEvents::SUCCESS => ['onUISuccess', -255], // After PricingPlugin
            AdmissionCheckEvent::EVENT_NAME => 'onAdmissionCheck',
        ];
    }

    public function onAdminEnrollmentEdit(EnrollmentEditEvent $event)
    {
        if(!$event->getEnrollment()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $this->buildEnrollmentEditForm($event, self::PLUGIN_NAME)
            ->add('used', CheckboxType::class, [
                'required' => false,
                'label' => 'plugin.admission_check.label.used',
                'attr' => [
                    'help_text' => 'plugin.admission_check.used.help_text',
                    'align_with_widget' => true,
                ]
            ])
        ;
    }

    public function onAdminEnrollmentEditSubmit(EnrollmentEditSubmitEvent $event)
    {
        if(!$event->getEnrollment()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $this->submitEnrollmentEditForm($event, self::PLUGIN_NAME);
    }

    public function onAdminEnrollmentGet(EnrollmentTemplateEvent $event)
    {
        if(!$event->getEnrollment()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'AdmissionCheckPlugin', 'Admin/Enrollment/get', 'html', 'twig'), [
            'pluginData' => $event->getEnrollment()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onAdminEnrollmentList(EnrollmentListEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        $event->setSimpleFacet('plugin.admission_check.facet.used', 'ticket', [
            'plugin.admission_check.facet.used.used_only' => [self::PLUGIN_NAME => ['status' => 'used']],
            'plugin.admission_check.facet.used.unused_only' => [self::PLUGIN_NAME => ['status' => 'unused']],
            'plugin.facet.all' => [self::PLUGIN_NAME => ['status' => null]],
        ]);

        $queryData = $event->getQueryString()->get(self::PLUGIN_NAME, []);

        switch(isset($queryData['status'])?$queryData['status']:null) {
            case 'used':
                $event->addFilter(function(Enrollment $enrollment) {
                    $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                    if(!$pluginData || !isset($pluginData['used']))
                        return false;
                    return $pluginData['used'];
                });
                break;
            case 'unused':
                $event->addFilter(function(Enrollment $enrollment) {
                    $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                    if(!$pluginData || !isset($pluginData['used']))
                        return true;
                    return $pluginData['used'];
                });
        }

        $event->setTemplatingField(['html'], 'admission_check.used', 'Used for admission', new TemplateReference('PluginBundle', 'AdmissionCheckPlugin', 'Admin/list/used', 'html', 'twig'));
        $event->setField(['csv'], 'admission_check.used', new CallbackTableColumnDefinition('Used for admission', function(array $data) {
            $enrollment = $data['data'];
            /* @var $enrollment Enrollment */
            if($enrollment->getPluginData()->has(self::PLUGIN_NAME)) {
                $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                if(isset($pluginData['used'])&&$pluginData['used']) {
                    return 'TRUE';
                } else {
                    return 'FALSE';
                }
            }
            return 'UNKNOWN';
        }));
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME, false);
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onUISuccess(EnrollmentTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $signature = Util::base64_encode_urlsafe(hash_hmac('sha256', $event->getEnrollment()->getId(), $this->secret, true));
        $event->addTemplate(new TemplateReference('PluginBundle', 'AdmissionCheckPlugin', 'UI/success', 'html', 'twig'), [
            'signature' => $signature,
            'enrollment_id' => Util::shortuuid_encode($event->getEnrollment()->getId()),
        ]);
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'AdmissionCheckPlugin', 'Admin/get', 'html', 'twig'));
    }

    public function onAdmissionCheck(AdmissionCheckEvent $event)
    {
        if(!$event->getEnrollment()->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getEnrollment()->getPluginData()->get(self::PLUGIN_NAME);
        if(!$pluginData || !isset($pluginData['used']) || !$pluginData['used']) {
            $event->addReasonedVote(AdmissionCheckEvent::VALIDITY_GRANT, self::PLUGIN_NAME, 'plugin.admission_check.reason.unused');
        } else {
            $event->addReasonedVote(AdmissionCheckEvent::VALIDITY_DENY, self::PLUGIN_NAME, 'plugin.admission_check.reason.used');
        }
    }
}

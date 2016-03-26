<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer;
use PluginBundle\Form\IgnoreTransformErrorsTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ReversedTransformer;

class DivertEnrollmentsPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'divert_enrollments';
    use PluginConfigurationHelperTrait;
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * DivertEnrollmentsPluginListener constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            FormEvents::SUBMIT => ['onFormSubmit', -100], // After all other plugins, so they don't get confused about the form on the enrollment changing underneath them
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('target', EntityType::class, [
                'label' => 'plugin.divert_enrollments.conf.target',
                'class' => 'AppBundle\Entity\Form',
                'choice_label' => 'name',
                'required' => false,
            ])
            ->get('target')
            ->addModelTransformer(new IgnoreTransformErrorsTransformer(new ReversedTransformer(new EntityToIdObjectTransformer($this->em, 'AppBundle:Form'))))
            ->addModelTransformer(new CallbackTransformer(function($object) {
                if($object)
                    return ['id' => $object];
                return null;
            }, function($object) {
                return $object;
            }))
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'DivertEnrollmentsPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        $enrollment = $event->getEnrollment();
        if(!$enrollment->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return false;
        $targetFormId = $enrollment->getForm()->getPluginData()->get(self::PLUGIN_NAME)['target'];
        $targetForm = $this->em->find('AppBundle:Form', $targetFormId);
        if(!$targetForm)
            throw new NoResultException();

        $enrollment->getPluginData()->add(self::PLUGIN_NAME, ['source_form_'.$targetFormId=>$enrollment->getForm()->getId()]);
        // Reuse role differentiation UIEvents::SUCCESS switching to display the same form as was used for enrollment
        $enrollment->getPluginData()->add('role_differentiation', ['used_form_'.$targetFormId=>$enrollment->getForm()->getId()]);
        $enrollment->setForm($targetForm);
    }
}

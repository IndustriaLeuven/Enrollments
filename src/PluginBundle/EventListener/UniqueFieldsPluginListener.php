<?php

namespace PluginBundle\EventListener;

use AppBundle\Event\Admin\EnrollmentEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\BootstrapCollectionType;
use PluginBundle\Entity\UniqueFieldDataRepository;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;

class UniqueFieldsPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'unique_fields';
    use PluginConfigurationHelperTrait;

    /**
     * @var UniqueFieldDataRepository
     */
    private $repo;

    /**
     * UniqueFieldsPluginListener constructor.
     * @param UniqueFieldDataRepository $repo
     */
    public function __construct(UniqueFieldDataRepository $repo)
    {
        $this->repo = $repo;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            FormEvents::SUBMIT => ['onFormSubmit', 10], // Before CountEnrollmentsPlugin
            AdminEvents::ENROLLMENT_DELETE => 'onAdminEnrollmentDelete',
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('fields', BootstrapCollectionType::class, [
                'label' => 'plugin.unique_fields.conf.fields',
                'allow_add' => true,
                'add_button_text' => 'plugin.unique_fields.conf.fields.add_button',
                'allow_delete' => true,
                'delete_button_text' => 'plugin.unique_fields.conf.fields.delete_button',
                'type' => TextType::class,
            ])
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'UniqueFieldsPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $uniqueFields = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['fields'];
        $conflictingFields = $this->repo->checkUniqueData($event->getEnrollment(), $uniqueFields);
        foreach($conflictingFields as $conflictingField) {
            $fieldPath = explode('.', $conflictingField);
            $field = $event->getSubmittedForm();
            foreach($fieldPath as $fieldName)
                if($field->has($fieldName))
                    $field = $field->get($fieldName);
            $formError = new FormError('plugin.unique_fields.error.duplicate_value');
            $field->addError($formError);
        }
        switch($event->getType()) {
            case $event::TYPE_CREATE:
                $this->repo->addUniqueData($event->getEnrollment(), $uniqueFields);
                break;
            case $event::TYPE_EDIT:
                $this->repo->setUniqueData($event->getEnrollment(), $uniqueFields);
        }
    }

    public function onAdminEnrollmentDelete(EnrollmentEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $this->repo->removeUniqueData($event->getEnrollment());
    }
}

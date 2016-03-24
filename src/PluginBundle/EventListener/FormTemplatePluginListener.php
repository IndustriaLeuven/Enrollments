<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Form\FinderChoiceLoader;
use AppBundle\Plugin\Table\CallbackTableColumnDefinition;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\BootstrapCollectionType;
use PluginBundle\Form\FormDefinitionInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormTemplatePluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'form_template';
    use PluginConfigurationHelperTrait;

    /**
     * @var string
     */
    private $searchDir;

    /**
     * FormTemplatePluginListener constructor.
     * @param string $searchDir
     */
    public function __construct($searchDir)
    {
        $this->searchDir = $searchDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            AdminEvents::ENROLLMENT_LIST => [
                ['onAdminEnrollmentList', 1],
                ['onAdminEnrollmentListRemoveData', -1],
            ],
            FormEvents::BUILD => 'onFormBuild',
            FormEvents::SUBMIT => 'onFormSubmit',
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('formType', ChoiceType::class, [
                'label' => 'plugin.form_template.conf.formType',
                'choice_loader' => new FinderChoiceLoader(Finder::create()->files()->in($this->searchDir), '.php'),
            ])
            ->add('admin_enrollment_list_fields', BootstrapCollectionType::class, [
                'label' => 'plugin.form_template.conf.admin_enrollment_list_fields',
                'allow_add' => true,
                'add_button_text' => 'plugin.form_template.conf.admin_enrollment_list_field.add_button',
                'allow_delete' => true,
                'delete_button_text' => 'plugin.form_template.conf.admin_enrollment_list_field.delete_button',
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'FormTemplatePlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);

    }

    public function onAdminEnrollmentList(EnrollmentListEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        if(!isset($pluginData['admin_enrollment_list_fields']))
            return;
        foreach($pluginData['admin_enrollment_list_fields'] as $field) {
            $event->setField(EnrollmentListEvent::ALL_TYPES, 'data.'.$field, new CallbackTableColumnDefinition($field, function(array $data) {
                $enrollment = $data['data'];
                /* @var $enrollment Enrollment */
                $flattenedData = $enrollment->getFlattenedData();
                if(isset($flattenedData[$data['fieldName']]))
                    return $flattenedData[$data['fieldName']];
                return '';
            }, [
                'fieldName' => $field,
            ]));
        }
    }

    public function onAdminEnrollmentListRemoveData(EnrollmentListEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        if(!isset($pluginData['admin_enrollment_list_fields']))
            return;
        if(count($pluginData['admin_enrollment_list_fields']) == 0)
            return;
        $event->removeField(EnrollmentListEvent::ALL_TYPES, '_.data');
    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $callback = @include $this->searchDir.'/'.$configuration['formType'];
        if(!$callback)
            throw new FileNotFoundException('File '.$configuration['formType'].' does not exist.');
        if(!is_callable($callback))
            throw new \BadFunctionCallException('Callback returned from '. $configuration['formType'].' is not callable.');
        $callback($event->getFormBuilder());
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $callback = @include $this->searchDir.'/'.$configuration['formType'];
        if(!$callback)
            throw new FileNotFoundException('File '.$configuration['formType'].' does not exist.');
        if($callback instanceof FormDefinitionInterface)
            $callback->handleSubmission($event->getSubmittedForm(), $event->getEnrollment());
    }
}

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
use Braincrafted\Bundle\BootstrapBundle\Session\FlashMessage;
use PluginBundle\Form\FormDefinition;
use PluginBundle\Form\FormDefinitionInterface;
use PluginBundle\Form\NullFormDefinition;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;

class FormTemplatePluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'form_template';
    use PluginConfigurationHelperTrait;

    /**
     * @var string
     */
    private $searchDir;
    /**
     * @var FlashMessage
     */
    private $flashMessage;
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * FormTemplatePluginListener constructor.
     *
     * @param string $searchDir
     * @param FlashMessage $flashMessage
     * @param FormFactory $formFactory
     */
    public function __construct($searchDir, FlashMessage $flashMessage, FormFactory $formFactory)
    {
        $this->searchDir = $searchDir;
        $this->flashMessage = $flashMessage;
        $this->formFactory = $formFactory;
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
        $pluginForm = $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('formType', ChoiceType::class, [
                'label' => 'plugin.form_template.conf.formType',
                'choice_loader' => new FinderChoiceLoader(Finder::create()
                        ->files()
                        ->in($this->searchDir)
                        ->name('*.php')
                        ->notName('_*')),
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
        if(!$event->isNew()) {
            $formDefinition = new NullFormDefinition();
            $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
            if(isset($pluginData['formType']))
                $formDefinition = $this->getFormDefinitionSafe($pluginData['formType']);
            $pluginForm->add('config', FormType::class, [
                'label' => 'plugin.form_template.conf.config'
            ]);
            $formDefinition->buildConfigForm($pluginForm->get('config'));
            if($pluginForm->get('config')->count() == 0) {
                // If the form template does not add any configuration, remove the config form
                $pluginForm->remove('config');
            }
        }
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $prevConfig = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $prevFormType = null;
        if(isset($prevConfig['formType']))
            $prevFormType = $prevConfig['formType'];
        $this->submitPluginForm($event, self::PLUGIN_NAME);
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return; // Plugin is not enabled

        $newConfig = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $newFormType = null;
        if(isset($newConfig['formType']))
            $newFormType = $newConfig['formType'];

        if($prevFormType !== $newFormType) {
            // Changed form type
            $formDefinition = $this->getFormDefinitionSafe($newFormType);
            $formBuilder = $this->formFactory->createBuilder();
            $formDefinition->buildConfigForm($formBuilder);
            // Check if new form type has config options
            if($formBuilder->count() > 0) {
                $this->flashMessage->alert('plugin.form_template.alert.config');
            }
            // Clear existing form template config
            unset($newConfig['config']);
            $event->getForm()->getPluginData()->set(self::PLUGIN_NAME, $newConfig);
        }
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

    /**
     * Fetches a form definition from a form definition file
     * @param string $formType
     * @return FormDefinition
     */
    private function getFormDefinition($formType) {
        $formDefinition = @include $this->searchDir.'/'.$formType;
        if(!$formDefinition) {
            throw new FileNotFoundException('File '.$formType.' does not exist.');
        }
        if(is_callable($formDefinition)) // Wrap function in a form definition class
            $formDefinition = new FormDefinition($formDefinition);
        if(!($formDefinition instanceof FormDefinitionInterface))
            throw new \DomainException('Callback returned from '.$formType.' is not a function or an instance of '.FormDefinitionInterface::class);
        return $formDefinition;
    }

    private function getFormDefinitionSafe($formType) {
        try {
            return $this->getFormDefinition($formType);
        } catch(\Exception $e) {
            return new NullFormDefinition($e);
        }
        catch(\Error $e) {
            return new NullFormDefinition($e);
        }
    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $formDefinition = $this->getFormDefinition($configuration['formType']);
        $config = isset($configuration['config'])?$configuration['config']:[];
        $formDefinition->buildForm($event->getFormBuilder(), $config);
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $formDefinition = $this->getFormDefinition($configuration['formType']);
        $config = isset($configuration['config'])?$configuration['config']:[];
        $formDefinition->handleSubmission($event->getSubmittedForm(), $event->getEnrollment(), $config);
    }
}

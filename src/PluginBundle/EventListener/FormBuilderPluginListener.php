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
use PluginBundle\Form\FormBuilderPluginConfigFormFieldType;
use PluginBundle\Form\FormDefinitionInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class FormBuilderPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'form_builder';
    use PluginConfigurationHelperTrait;

    /**
     * @var ExpressionLanguage
     */
    private $constraintsExpressionLanguage;

    /**
     * @var ExpressionLanguage
     */
    private $fieldsExpressionLanguage;

    /**
     * FormBuilderPluginListener constructor.
     */
    public function __construct()
    {
        $this->constraintsExpressionLanguage = new ExpressionLanguage();
        $this->fieldsExpressionLanguage = new ExpressionLanguage();
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
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('fields', BootstrapCollectionType::class, [
                'allow_add' => true,
                'add_button_text' => 'Add field',
                'allow_delete' => true,
                'delete_button_text' => 'Remove field',
                'required' => false,
                'type' => FormBuilderPluginConfigFormFieldType::class,
                'options' => [
                    'constraints_expressionLanguage' => $this->constraintsExpressionLanguage,
                    'options_expressionLanguage' => $this->fieldsExpressionLanguage,
                    'attr' => [
                        'style' => 'horizontal',
                    ],
                ],
            ])
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
        $pluginDataBag = $event->getForm()->getPluginData();
        if($pluginDataBag->has(self::PLUGIN_NAME)) {
            $data = $pluginDataBag->get(self::PLUGIN_NAME);
            foreach($data['fields'] as &$field) {
                $field['options_compiled'] =
                    (array)$this->fieldsExpressionLanguage->evaluate($field['options']) + [
                        'required' => isset($field['required'])&&$field['required'],
                        'disabled' => isset($field['disabled'])&&$field['disabled'],
                    ];
                foreach($field['constraints'] as &$constraint) {
                    $field['options_compiled']['constraints'][] = new $constraint['type']($this->constraintsExpressionLanguage->evaluate($field['options']));
                }
            }
            $pluginDataBag->set(self::PLUGIN_NAME, $data);
        }
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'FormBuilderPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);

    }

    public function onAdminEnrollmentList(EnrollmentListEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $listFields = array_filter($pluginData['fields'], function($field) {
            return isset($field['show_in_enrollment_list']) && $field['show_in_enrollment_list'];
        });
        foreach($listFields as $field) {
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
        $listFields = array_filter($pluginData['fields'], function($field) {
            return isset($field['show_in_enrollment_list']) && $field['show_in_enrollment_list'];
        });
        if(count($listFields) == 0)
            return;
        $event->removeField(EnrollmentListEvent::ALL_TYPES, '_.data');
    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        $formBuilder = $event->getFormBuilder();
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);

        foreach($configuration['fields'] as $field) {
            $formBuilder->add($field['name'], $field['type'], $field['options_compiled']);
        }

    }
}

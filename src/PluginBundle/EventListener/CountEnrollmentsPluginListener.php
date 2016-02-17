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
use AppBundle\Event\UIEvents;
use PluginBundle\Entity\EnrollmentCountRepository;
use PluginBundle\ExpressionLanguage\LogicExpressionProvider;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class CountEnrollmentsPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'count_enrollments';
    use PluginConfigurationHelperTrait;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var EnrollmentCountRepository
     */
    private $repo;

    /**
     * CountEnrollmentsPluginListener constructor.
     * @param EnrollmentCountRepository $repo
     */
    public function __construct(EnrollmentCountRepository $repo)
    {
        $this->repo = $repo;
        $this->expressionLanguage = new ExpressionLanguage();
        $this->expressionLanguage->registerProvider(new LogicExpressionProvider());
    }


    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminFormGet',
            AdminEvents::ENROLLMENT_DELETE => 'onAdminEnrollmentDelete',
            UIEvents::FORM => ['onUIForm', 256], // Has to be before RoleDifferentiationPluginListener, because enrollments are counted on the root form
            FormEvents::SUBMIT => 'onFormSubmit',
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('maxEnrollments', NumberType::class, [
                'required' => false,
                'constraints' => [
                    new GreaterThan(0),
                ],
            ])
            ->add('denyEnrollments', CheckboxType::class, [
                'attr' => [
                    'help_text' => 'Deny further enrollments when the maximum number is reached',
                ],
                'required' => false,
            ])
            ->add('countExpression', TextareaType::class, [
                'empty_data' => 1,
                'attr' => [
                    'help_text' => "Available functions: if(condition, ifTrue, ifFalse)\n".
                        "Available variables: formData",
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new \PluginBundle\Constraints\ExpressionLanguage([
                        'expressionLanguage' => $this->expressionLanguage,
                        'variables' => ['formData'],
                    ]),
                ],
            ])
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminFormGet(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'CountEnrollmentsPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onUIForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $enrollmentCount = $this->repo->getEnrollmentCount($event->getForm());
        $event->addTemplate(new TemplateReference('PluginBundle', 'CountEnrollmentsPlugin', 'UI/form', 'html', 'twig'), [
            'enrollmentCount' => $enrollmentCount,
            'pluginData' => $pluginData,
        ]);
        if($enrollmentCount >= $pluginData['maxEnrollments'] && $pluginData['denyEnrollments'])
            $event->stopPropagation();
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        $form = $event->getEnrollment()->getForm(); // Get the root form, not a differentiated form, in case RoleDifferentiationPlugin is in use
        if(!$form->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $form->getPluginData()->get(self::PLUGIN_NAME);
        $thisEnrollmentCount = (int)$this->expressionLanguage->evaluate($pluginData['countExpression'], [
            'formData' => $event->getSubmittedForm()->getData(),
        ]);
        switch($event->getType()) {
            case SubmitFormEvent::TYPE_CREATE:
                $enrollmentCount = $this->repo->getEnrollmentCount($form);
                if($pluginData['denyEnrollments']) {
                    if($enrollmentCount >= $pluginData['maxEnrollments']) {
                        $event->getSubmittedForm()->addError(new FormError('This event is full.'));
                    } elseif(($enrollmentCount + $thisEnrollmentCount) > $pluginData['maxEnrollments']) {
                        $event->getSubmittedForm()->addError(new FormError('There are not enough spots left to add this enrollment.'));
                    }
                }
                $this->repo->addEnrollmentCount($event->getEnrollment(), $thisEnrollmentCount);
                break;
            default:
                $this->repo->setEnrollmentCount($event->getEnrollment(), $thisEnrollmentCount);
        }
    }

    public function onAdminEnrollmentDelete(EnrollmentEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $this->repo->setEnrollmentCount($event->getEnrollment(), 0);
    }
}

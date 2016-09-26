<?php

namespace PluginBundle\EventListener;

use AdamQuaile\Bundle\FieldsetBundle\Form\FieldsetType;
use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Plugin\PluginDataBag;
use Braincrafted\Bundle\BootstrapBundle\Session\FlashMessage;
use PluginBundle\Constraints\TwigTemplate;
use PluginBundle\Event\EmailEvent;
use PluginBundle\Event\PricingPaidAmountEditedEvent;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'email';
    const EMAIL_TYPE_ENROLL = 'enroll';
    const EMAIL_TYPE_PAID = 'paid';
    const EMAIL_TYPE_PAID_PARTIALLY = 'paid_partially';
    use PluginConfigurationHelperTrait;

    static private $mailTypeToEventsMap = [
        self::EMAIL_TYPE_ENROLL => EmailEvent::ENROLL_EVENT,
        self::EMAIL_TYPE_PAID => EmailEvent::PAID_EVENT,
        self::EMAIL_TYPE_PAID_PARTIALLY => EmailEvent::PAID_PARTIALLY_EVENT,
    ];

    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var FlashMessage
     */
    private $flashMessage;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $emailSender;

    /**
     * EmailPluginListener constructor.
     */
    public function __construct(\Twig_Environment $twig, FlashMessage $flashMessage, \Swift_Mailer $mailer, $emailSender)
    {
        $this->twig = new \Twig_Environment($twig->getLoader());
        $this->twig->mergeGlobals($twig->getGlobals());
        foreach($twig->getExtensions() as $extension)
            if(!$this->twig->hasExtension($extension->getName()))
                $this->twig->addExtension($extension);
        $sandboxPolicy = new \Twig_Sandbox_SecurityPolicy([
            // Allowed tags
            'do',
            'if',
            'for',
            'set',
            'spaceless',
            'verbatim'
        ], [
            // Allowed filters
            'abs',
            'batch',
            'capitalize',
            'convert_encoding',
            'date',
            'default',
            'escape',
            'first',
            'format',
            'join',
            'keys',
            'last',
            'length',
            'lower',
            'merge',
            'number_format',
            'raw',
            'replace',
            'reverse',
            'round',
            'slice',
            'sort',
            'split',
            'striptags',
            'title',
            'trim',
            'upper',
            'url_encode',
        ], [
            // Allowed methods
            Form::class => [
                'getId',
                'getName',
                'getPluginData',
            ],
            Enrollment::class => [
                'getId',
                'getData',
                'getPluginData',
                'getForm',
                'getCreatedAt',
            ],
            PluginDataBag::class => [
                'get',
                'has'
            ],
        ], [
            // Allowed properties
        ], [
            // Allowed functions
            'attribute',
            'constant',
            'cycle',
            'date',
            'max',
            'min',
            'random',
            'range',
            'url',
            'qrcode_data_uri',
        ]);
        $this->twig->addExtension(new \Twig_Extension_Sandbox($sandboxPolicy, true));
        foreach($twig->getFilters() as $filter)
            if($filter instanceof \Twig_SimpleFilter)
                $this->twig->addFilter($filter);
        foreach($twig->getFunctions() as $function)
            if($function instanceof \Twig_SimpleFunction)
                $this->twig->addFunction($function);
        $this->flashMessage = $flashMessage;
        $this->mailer = $mailer;
        $this->emailSender = $emailSender;
    }


    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminFormGet',
            FormEvents::SUBMIT => ['onFormSubmit', -255], // After everything that might cancel the form event
            PricingPaidAmountEditedEvent::EVENT_NAME => 'onPluginPricingPaidAmountEdited',
            EmailEvent::ENROLL_EVENT => 'onEmail',
            EmailEvent::PAID_EVENT => 'onEmail',
            EmailEvent::PAID_PARTIALLY_EVENT => 'onEmail',
        ];
    }


    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        if(!$event->isNew())
            $this->upgradePluginConfiguration($event->getForm());
        $formBuilder = $this->buildPluginForm($event, self::PLUGIN_NAME);
        $this->buildPluginFormForEmailType($formBuilder, self::EMAIL_TYPE_ENROLL, ['form', 'enrollment']);
        $this->buildPluginFormForEmailType($formBuilder, self::EMAIL_TYPE_PAID, ['enrollment', 'previous_paid_amount', 'previous_total_amount']);
        $this->buildPluginFormForEmailType($formBuilder, self::EMAIL_TYPE_PAID_PARTIALLY, ['enrollment', 'previous_paid_amount', 'previous_total_amount']);
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->upgradePluginConfiguration($event->getForm());
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminFormGet(SubmittedFormTemplateEvent $event)
    {
        $this->upgradePluginConfiguration($event->getForm());
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'EmailPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onEmail(EmailEvent $event, $eventName)
    {
        // No check if plugin is enabled here, because the EmailEvent is emitted from this plugin.
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $formData = $event->getEnrollment()->getData();
        $mailType = array_search($eventName, self::$mailTypeToEventsMap);
        // Render email subject & body
        $emailSubject = $this->twig->createTemplate($pluginData[$mailType]['emailSubject'])->render($event->getVariables());
        $emailBody = $this->twig->createTemplate($pluginData[$mailType]['emailBody'])->render($event->getVariables());

        $event->getMessage()
            ->setSubject($emailSubject)
            ->setBody($emailBody, 'text/html')
            ->addPart(html_entity_decode(strip_tags($emailBody)), 'text/plain')
            ->addTo($formData['email'], isset($formData['name'])?$formData['name']:null)
            ->setFrom($this->emailSender)
        ;
    }


    public function onFormSubmit(SubmitFormEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $this->upgradePluginConfiguration($event->getForm());
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        if($event->getSubmittedForm()->getErrors(true)->count()) // Errors have been added by previous plugins, don't send a confirmation email
            return;

        $templateData = [
            'enrollment' => $event->getEnrollment(),
            'form' => $event->getForm(),
        ];

        try {
            $this->sendForEmailType($event->getForm(), $event->getEnrollment(), self::EMAIL_TYPE_ENROLL, $templateData, $eventDispatcher);
        } catch(\Exception $error) {
            $this->flashMessage->alert('Unfortunately, an error occurred while sending your confirmation mail: '.$error->getMessage());
        }

    }

    public function onPluginPricingPaidAmountEdited(PricingPaidAmountEditedEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $form = $event->getForm();
        $this->upgradePluginConfiguration($form);
        if (!$form->getPluginData()->has(self::PLUGIN_NAME))
            return;

        $enrollment = $event->getEnrollment();
        $pricingData = $enrollment->getPluginData()->get('pricing');
        if (!$pricingData) // WTF? No pricing data on this enrollment. Maybe
            return;

        $templateData = [
            'enrollment' => $enrollment,
            'previous_paid_amount' => $event->getPreviousPaidAmount(),
            'previous_total_amount' => $event->getPreviousTotalAmount(),
        ];

        try {
            if ($pricingData['paidAmount'] < $pricingData['totalPrice']) {
                $this->sendForEmailType($form, $enrollment, self::EMAIL_TYPE_PAID_PARTIALLY, $templateData, $eventDispatcher);
            } else {
                $this->sendForEmailType($form, $enrollment, self::EMAIL_TYPE_PAID, $templateData, $eventDispatcher);
            }
        } catch(\Exception $ex) {
            $this->flashMessage->alert('An error occurred while sending the payment amount change email: '.$ex->getMessage());
        }
    }

    /**
     * Adds the edit form for an email type to the plugin configuration form
     * @param FormBuilderInterface $formBuilder
     * @param string $name Email type name
     * @param string[] $variables Array of names of available variables
     */
    private function buildPluginFormForEmailType(FormBuilderInterface $formBuilder, $name, $variables)
    {
        $formBuilder
            ->add($name, FieldsetType::class, [
                'legend' => 'plugin.'.self::PLUGIN_NAME.'.conf.'.$name.'.title',
                'label' => false,
                'inherit_data' => false,
                'validation_groups' => function(FormInterface $form) {
                    return $form->get('enable')->getData()?['Default']:false;
                }
            ])
            ->get($name)
            ->add('enable', CheckboxType::class, [
                'label' => 'plugin.label.enabled',
                'required' => false,
            ])
            ->add('emailSubject', TextareaType::class, [
                'label' => 'plugin.email.conf.email_subject',
                'attr' => [
                    'help_text' => 'plugin.email.twig_template',
                    'help_text_arguments' => implode(', ', $variables),
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new TwigTemplate([
                        'twig' => $this->twig,
                    ]),
                ],
            ])
            ->add('emailBody', TextareaType::class, [
                'label' => 'plugin.email.conf.email_body',
                'attr' => [
                    'help_text' => 'plugin.email.twig_template',
                    'help_text_arguments' => implode(', ', $variables),
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new TwigTemplate([
                        'twig' => $this->twig,
                    ]),
                ],
            ])
        ;
    }

    /**
     * Sends an email message for an email type
     *
     * @param Form $form Form to get the templates from
     * @param Enrollment $enrollment Enrollment to get the name and email data from
     * @param string $name Email type name
     * @param array $variables Map of names of variables to their content
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     */
    private function sendForEmailType(Form $form, Enrollment $enrollment, $name, $variables, EventDispatcherInterface $eventDispatcher)
    {
        $pluginData = $form->getPluginData()->get(self::PLUGIN_NAME);
        if(!isset($pluginData[$name])||!isset($pluginData[$name]['enable'])||!$pluginData[$name]['enable'])
            // Plugin is not enabled
            return;
        $formData = $enrollment->getData();
        if(!isset($formData['email'])) // There is no email address to send the confirmation to
            return;

        if(!isset(self::$mailTypeToEventsMap[$name]))
            return;

        $event = new EmailEvent($form, $enrollment, $variables);

        $eventDispatcher->dispatch(self::$mailTypeToEventsMap[$name], $event);

        // Send email message
        $this->mailer->send($event->getMessage());
    }

    private function upgradePluginConfiguration(Form $form)
    {
        if(!$form->getPluginData()->has('email_confirmation'))
            return;
        $form->getPluginData()->add(self::PLUGIN_NAME, [
            self::EMAIL_TYPE_ENROLL => ['enable'=>true]+ $form->getPluginData()->get('email_confirmation'),
        ]);
        $form->getPluginData()->remove('email_confirmation');
    }
}

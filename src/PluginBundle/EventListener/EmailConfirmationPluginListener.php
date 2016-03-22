<?php

namespace PluginBundle\EventListener;

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
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmailConfirmationPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'email_confirmation';
    use PluginConfigurationHelperTrait;

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
     * EmailConfirmationPluginListener constructor.
     */
    public function __construct(\Twig_Environment $twig, FlashMessage $flashMessage, \Swift_Mailer $mailer, $emailSender)
    {
        $this->twig = clone $twig;
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
        ]);
        $this->twig->addExtension(new \Twig_Extension_Sandbox($sandboxPolicy, true));
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
        ];
    }


    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('emailSubject', TextareaType::class, [
                'attr' => [
                    'help_text' => "Twig template code, for syntax and available functions, have a look at the <a href='http://twig.sensiolabs.org/doc/templates.html'>template designer documentation</a><br>".
                        "Available variables: form, enrollment",
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new \PluginBundle\Constraints\TwigTemplate([
                        'twig' => $this->twig,
                    ]),
                ],
            ])
            ->add('emailBody', TextareaType::class, [
                'attr' => [
                    'help_text' => "Twig template code, for syntax and available functions, have a look at the <a href='http://twig.sensiolabs.org/doc/templates.html'>template designer documentation</a><br>".
                        "Available variables: form, enrollment",
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new \PluginBundle\Constraints\TwigTemplate([
                        'twig' => $this->twig,
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'EmailConfirmationPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        if($event->getSubmittedForm()->getErrors(true)->count()) // Errors have been added by previous plugins, don't send a confirmation email
            return;

        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $formData = $event->getEnrollment()->getData();

        if(!isset($formData['email'])) // There is no email address to send the confirmation to
            return;

        $templateData = [
            'enrollment' => $event->getEnrollment(),
            'form' => $event->getForm(),
        ];

        try {
            $emailSubject = $this->twig->createTemplate($pluginData['emailSubject'])->render($templateData);
            $emailBody = $this->twig->createTemplate($pluginData['emailBody'])->render($templateData);
            $message = \Swift_Message::newInstance($emailSubject, $emailBody, 'text/html')
                ->addPart(html_entity_decode(strip_tags($emailBody)), 'text/plain')
                ->addTo($formData['email'], isset($formData['name'])?$formData['name']:null)
                ->setFrom($this->emailSender);

            $this->mailer->send($message);
        } catch(\Exception $error) {
            $this->flashMessage->alert('Unfortunately, an error occurred while sending your confirmation mail: '.$error->getMessage());
        }

    }
}

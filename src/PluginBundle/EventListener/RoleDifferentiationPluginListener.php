<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\FormTemplateEvent;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use Doctrine\ORM\EntityManager;
use PluginBundle\Form\RoleDifferentiationPluginConfigType;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RoleDifferentiationPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'role_differentiation';
    use PluginConfigurationHelperTrait;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var AccessDecisionManagerInterface
     */
    private $accessDecisionManager;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * RoleDifferentiationPluginListener constructor.
     * @param ExpressionLanguage $expressionLanguage
     * @param EntityManager $em
     * @param AccessDecisionManagerInterface $accessDecisionManager
     */
    public function __construct(ExpressionLanguage $expressionLanguage, EntityManager $em, AccessDecisionManagerInterface $accessDecisionManager, TokenStorageInterface $tokenStorage)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->em = $em;
        $this->accessDecisionManager = $accessDecisionManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $expression
     * @return \Symfony\Component\ExpressionLanguage\ParsedExpression
     */
    private function parseExpression($expression)
    {
        return $this->expressionLanguage->parse($expression, [
            'token',
            'user',
            'object',
            'subject',
            'roles',
            'trust_resolver',
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            UIEvents::FORM => ['onUIForm', 255],
            UIEvents::SUCCESS => ['onUISuccess', 255],
            FormEvents::SUBMIT => ['onFormSubmit', 255],
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('rules', 'bootstrap_collection', [
                'allow_add' => true,
                'allow_delete' => true,
                'type' => RoleDifferentiationPluginConfigType::class,
                'options' => [
                    'expression_language_validator' => new Callback(\Closure::bind(
                        function($condition, ExecutionContextInterface $executionContext) {
                            try {
                                if($condition) {
                                    $this->parseExpression($condition);
                                }
                            } catch(SyntaxError $error) {
                                $executionContext->addViolation($error->getMessage());
                            }
                        }
                        , $this)),
                    'em' => $this->em,
                ]
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'RoleDifferentiationPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onUIForm(SubmittedFormTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $this->doRoleDifferentiation($event, $eventName, $eventDispatcher, function(Form $form) use($event) {
            return new SubmittedFormTemplateEvent($form);
        }, function(SubmittedFormTemplateEvent $childEvent, SubmittedFormTemplateEvent $parentEvent) {
            $templates = $childEvent->getTemplates();
            foreach ($templates as $template)
                $parentEvent->addTemplate($template, $templates->getInfo());
            $parentEvent->setSubmittedForm($childEvent->getSubmittedForm());
        });
    }


    public function onUISuccess(EnrollmentTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $this->doPresetDifferentiation($event, $eventName, $eventDispatcher, $event->getEnrollment(), function (Form $form) use ($event) {
            return new EnrollmentTemplateEvent($form, $event->getEnrollment(), $event->isEditDisabled());
        }, function (EnrollmentTemplateEvent $childEvent, EnrollmentTemplateEvent $parentEvent) {
            $templates = $childEvent->getTemplates();
            foreach ($templates as $template)
                $parentEvent->addTemplate($template, $templates->getInfo());
            $parentEvent->setSubmittedForm($childEvent->getSubmittedForm());
        });
    }

    public function onFormSubmit(SubmitFormEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $pluginDataKey = $this->buildPluginDataKey($event);
        if($event->getType() === $event::TYPE_CREATE) {
            $this->doRoleDifferentiation($event, $eventName, $eventDispatcher, function (Form $form) use ($event, $pluginDataKey) {
                // Store this new form we are using in the plugin data of the enrollment
                $event->getEnrollment()->getPluginData()->add(self::PLUGIN_NAME, [$pluginDataKey => $form->getId()]);
                return new SubmitFormEvent($form, $event->getSubmittedForm(), $event->getEnrollment());
            }, function (SubmitFormEvent $childEvent, SubmitFormEvent $parentEvent) {
            });
        } elseif($event->getType() === $event::TYPE_EDIT) {
            $this->doPresetDifferentiation($event, $eventName, $eventDispatcher, $event->getEnrollment(), function (Form $form) use ($event) {
                return new SubmitFormEvent($form, $event->getSubmittedForm(), $event->getEnrollment(), $event->getType());
            }, function (SubmitFormEvent $childEvent, SubmitFormEvent $parentEvent) {

            });
        } else {
            throw new \LogicException('Unexpected form event type');
        }
    }

    /**
     * Applies differentiation based on a previously defined property on an enrollment
     * @param AbstractFormEvent $event Current event
     * @param string $eventName Name of current event
     * @param EventDispatcherInterface $eventDispatcher
     * @param Enrollment $enrollment The enrollment to do differentiation for
     * @param \Closure $eventFactory Callback to create a new child event. Takes the new form as argument.
     * @param \Closure $dataCopier copies data from the child event to the parent event, after it has been dispatched
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function doPresetDifferentiation(AbstractFormEvent $event, $eventName, EventDispatcherInterface $eventDispatcher, Enrollment $enrollment, \Closure $eventFactory, \Closure $dataCopier)
    {
        $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
        $pluginDataKey = $this->buildPluginDataKey($event);
        if(!$pluginData || !isset($pluginData[$pluginDataKey]))
            return;
        $formId = $pluginData[$pluginDataKey];
        $form = $this->em->find('AppBundle:Form', $formId);
        if(!$form)
            throw new NotFoundHttpException('Form with id '.$formId.' does not exist.');
        /* @var $form Form */
        $newEvent = $eventFactory($form);
        $eventDispatcher->dispatch($eventName, $newEvent);
        $dataCopier($newEvent, $event);
        $event->stopPropagation();
    }

    /**
     * Applies role differentiation rules
     *
     * Replaces the current event with an event with the form substituted by the target form of the first matching rule
     * @param AbstractFormEvent $event Current event
     * @param string $eventName Name of current event
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher
     * @param \Closure $eventFactory Callback to create a new child event. Takes the new form as argument.
     * @param \Closure $dataCopier copies data from the child event to the parent event, after it has been dispatched
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function doRoleDifferentiation(AbstractFormEvent $event, $eventName, EventDispatcherInterface $eventDispatcher, \Closure $eventFactory, \Closure $dataCopier)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        foreach($event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['rules'] as $rule) {
            // Check if the ACL condition matches
            if($this->accessDecisionManager->decide($this->tokenStorage->getToken(), [
                $this->parseExpression($rule['condition'])
            ])) {
                if(!$rule['target_form']) {
                    // If the target form is empty, this is an access denied condition
                    throw new AccessDeniedException('Form configuration denies access.');
                }
                $form = $this->em->find('AppBundle:Form', $rule['target_form']);
                if(!$form)
                    throw new NotFoundHttpException('Form with id '.$rule['target_form'].' does not exist.');
                /* @var $form Form */
                // Create new child event and dispatch it
                $childEvent = $eventFactory($form);
                /* @var $childEvent FormTemplateEvent */
                $eventDispatcher->dispatch($eventName, $childEvent);
                // Copy data from child event to parent event
                $dataCopier($childEvent, $event);
                // Stop propagation of parent event, we got all data already
                $event->stopPropagation();
                // Stop checking all other conditions, we only match on the first one
                break;
            }
        }
    }

    /**
     * Creates a per-form unique pluginData key, so recursive usage of role differentiation plugins is possible
     * @param AbstractFormEvent $originalEvent
     * @return string
     */
    private function buildPluginDataKey(AbstractFormEvent $originalEvent)
    {
        return 'used_form_'.$originalEvent->getForm()->getId();
    }
}

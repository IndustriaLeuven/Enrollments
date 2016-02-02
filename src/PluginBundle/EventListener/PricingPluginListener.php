<?php

namespace PluginBundle\EventListener;

use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\FormTemplateEvent;
use AppBundle\Event\UI\SuccessTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PricingPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'pricing';
    use PluginConfigurationHelperTrait;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->expressionLanguage = new ExpressionLanguage();
        $this->expressionLanguage->register('concat', function() {
            return '('.implode(')+(', func_get_args()).')';
        }, function() {
            $args = func_get_args();
            array_shift($args);
            return implode('', $args);
        });
        $this->expressionLanguage->register('if', function($condition, $trueExpr, $falseExpr) {
            return "(($condition)?($trueExpr):($falseExpr))";
        }, function($vars, $condition, $trueExpr, $falseExpr) {
            return $condition?$trueExpr:$falseExpr;
        });
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::SHOW_FORM => 'onAdminShowForm',
            UIEvents::FORM => ['onUIForm', -253],
            FormEvents::SUBMIT => 'onFormSubmit',
            UIEvents::SUCCESS => ['onUISuccess', -253],
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('formula', 'text', [
                'attr' => [
                    'help_text' => 'Available variables: formData, _locale',
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new Callback(\Closure::bind(
                        function($formula, ExecutionContextInterface $executionContext) {
                            try {
                                $this->expressionLanguage->parse($formula, ['formData', '_locale']);
                            } catch(SyntaxError $error) {
                                $executionContext->addViolation($error->getMessage());
                            }
                        }
                        , $this))
                ]
            ])
            ->add('payment_expression', 'text', [
                'attr' => [
                    'help_text' => 'Available variables: formData, totalPrice, _locale',
                ],
                'required' => false,
                'constraints' => [
                    new Callback(\Closure::bind(
                        function($expression, ExecutionContextInterface $executionContext) {
                            try {
                                if($expression) {
                                    $this->expressionLanguage->parse($expression, ['formData', 'totalPrice', '_locale']);
                                }
                            } catch(SyntaxError $error) {
                                $executionContext->addViolation($error->getMessage());
                            }
                        }
                        , $this))
                ]
            ])
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminShowForm(FormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'PricingPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onUIForm(FormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'PricingPlugin', 'UI/form', 'html', 'twig'), [
            'priceJavascipt' => $this->expressionLanguage->compile($event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['formula'], [
                'formData',
                '_locale',
            ]),
            'paymentDetailsJavascript' =>
                $event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['payment_expression']
                    ?$this->expressionLanguage->compile($event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['payment_expression'], [
                        'formData',
                        'totalPrice',
                        '_locale',
                    ])
                    :null
        ]);
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        $formData = $event->getSubmittedForm()->getData();
        $totalPrice = $this->expressionLanguage->evaluate($event->getForm()->getPluginData()->get(self::PLUGIN_NAME)['formula'], [
            'formData' => $formData,
            '_locale' => $this->requestStack->getMasterRequest()->attributes->get('_locale', 'en'),
        ]);
        $event->getEnrollment()->getPluginData()->set(self::PLUGIN_NAME,['totalPrice' => $totalPrice]);
    }

    public function onUISuccess(SuccessTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;


        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        if(isset($pluginData['payment_expression'])) {
            $totalPrice = $event->getEnrollment()->getPluginData()->get(self::PLUGIN_NAME)['totalPrice'];
            $paymentDetails = $this->expressionLanguage->evaluate(
                $pluginData['payment_expression'],
                [
                    'formData' => $event->getEnrollment()->getData(),
                    'totalPrice' => $totalPrice,
                    '_locale' => $this->requestStack->getMasterRequest()->attributes->get('_locale', 'en'),
                ]
            );

            $event->addTemplate(new TemplateReference('PluginBundle', 'PricingPlugin', 'UI/success', 'html', 'twig'), [
                'paymentDetails' => $paymentDetails,
            ]);
        }

    }
}

<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\Admin\EnrollmentBatchEvent;
use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentEditSubmitEvent;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use AppBundle\Plugin\Table\CallbackTableColumnDefinition;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use PluginBundle\ExpressionLanguage\LogicExpressionProvider;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\VarDumper\VarDumper;

class PricingPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'pricing';
    use PluginConfigurationHelperTrait;
    use EnrollmentEditHelperTrait;

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
        $this->expressionLanguage->registerProvider(new LogicExpressionProvider());
        $this->expressionLanguage->register('concat', function() {
            return '('.implode(')+(', func_get_args()).')';
        }, function() {
            $args = func_get_args();
            array_shift($args);
            return implode('', $args);
        });
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            AdminEvents::ENROLLMENT_LIST => 'onAdminEnrollmentList',
            AdminEvents::ENROLLMENT_BATCH => 'onAdminEnrollmentBatch',
            AdminEvents::ENROLLMENT_GET => 'onAdminEnrollmentGet',
            AdminEvents::ENROLLMENT_EDIT => 'onAdminEnrollmentEdit',
            AdminEvents::ENROLLMENT_EDIT_SUBMIT => 'onAdminEnrollmentEditSubmit',
            UIEvents::FORM => ['onUIForm', -253],
            FormEvents::SUBMIT => 'onFormSubmit',
            UIEvents::SUCCESS => ['onUISuccess', -253],
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('formula', TextareaType::class, [
                'label' => 'plugin.pricing.conf.formula',
                'attr' => [
                    'help_text' => 'plugin.pricing.conf.formula.help',
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new \PluginBundle\Constraints\ExpressionLanguage([
                        'expressionLanguage' => $this->expressionLanguage,
                        'variables' => ['formData', '_locale'],
                    ]),
                ]
            ])
            ->add('payment_expression', TextareaType::class, [
                'label' => 'plugin.pricing.conf.payment_expression',
                'attr' => [
                    'help_text' => 'plugin.pricing.conf.payment_expression.help',
                ],
                'required' => false,
                'constraints' => [
                    new \PluginBundle\Constraints\ExpressionLanguage([
                        'expressionLanguage' => $this->expressionLanguage,
                        'variables' => ['formData', 'totalPrice', '_locale'],
                    ]),
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'PricingPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onAdminEnrollmentList(EnrollmentListEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        $event->setSimpleFacet('plugin.pricing.facet.payment', 'money', [
            'plugin.pricing.facet.payment.paid_only' => ['pricing' => ['status' => 'paid']],
            'plugin.pricing.facet.payment.unpaid_only' => ['pricing' => ['status' => 'unpaid']],
            'plugin.facet.all' => ['pricing' => ['status' => null]],
        ]);

        $queryData = $event->getQueryString()->get('pricing', []);

        switch(isset($queryData['status'])?$queryData['status']:null) {
            case 'paid':
                $event->addFilter(function(Enrollment $enrollment) {
                    $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                    if(!$pluginData || !isset($pluginData['totalPrice']) || !isset($pluginData['paidAmount']))
                        return false;
                    return $pluginData['paidAmount'] >= $pluginData['totalPrice'];
                });
                break;
            case 'unpaid':
                $event->addFilter(function(Enrollment $enrollment) {
                    $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                    if(!$pluginData || !isset($pluginData['totalPrice']))
                        return false;
                    return !isset($pluginData['paidAmount']) || $pluginData['paidAmount'] < $pluginData['totalPrice'];
                });
        }

        $event->setTemplatingField(['html'], 'pricing.totalPrice', 'Price', new TemplateReference('PluginBundle', 'PricingPlugin', 'Admin/list/price', 'html', 'twig'));
        $event->setField(['csv'], 'pricing.totalPrice', new CallbackTableColumnDefinition('Price', function(array $data) {
            $enrollment = $data['data'];
            /* @var $enrollment Enrollment */
            if($enrollment->getPluginData()->has(self::PLUGIN_NAME)) {
                $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                if(isset($pluginData['totalPrice']))
                    return $pluginData['totalPrice'];
            }
            return '0';
        }));
        $event->setField(['csv'], 'pricing.paidAmount', new CallbackTableColumnDefinition('Paid amount', function(array $data) {
            $enrollment = $data['data'];
            /* @var $enrollment Enrollment */
            if($enrollment->getPluginData()->has(self::PLUGIN_NAME)) {
                $pluginData = $enrollment->getPluginData()->get(self::PLUGIN_NAME);
                if(isset($pluginData['paidAmount']))
                    return $pluginData['paidAmount'];
            }
            return '0';
        }));
    }

    public function onAdminEnrollmentBatch(EnrollmentBatchEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $plugin_name = self::PLUGIN_NAME;
        $event->setAction(self::PLUGIN_NAME.'_mark_paid', 'plugin.pricing.batch.mark_paid', function(Enrollment $enrollment) use($plugin_name) {
            if(!$enrollment->getPluginData()->has($plugin_name))
                return;
            $pluginData = $enrollment->getPluginData()->get($plugin_name);
            if(!isset($pluginData['paidAmount'])||$pluginData['paidAmount'] == 0)
                $enrollment->getPluginData()->add($plugin_name, ['paidAmount' => $pluginData['totalPrice']]);
        });

    }

    public function onAdminEnrollmentGet(EnrollmentTemplateEvent $event)
    {
        if(!$event->getEnrollment()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'PricingPlugin', 'Admin/Enrollment/get', 'html', 'twig'), [
            'pluginData' => $event->getEnrollment()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onAdminEnrollmentEdit(EnrollmentEditEvent $event)
    {
        if(!$event->getEnrollment()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $this->buildEnrollmentEditForm($event, self::PLUGIN_NAME)
            ->setData($event->getEnrollment()->getPluginData()->get(self::PLUGIN_NAME))
            ->add('totalPrice', MoneyType::class, [
                'label' => 'plugin.pricing.enrollment.totalPrice',
                'disabled' => true,
            ])
            ->add('paidAmount', MoneyType::class, [
                'label' => 'plugin.pricing.enrollment.paidAmount',
            ])
        ;
    }

    public function onAdminEnrollmentEditSubmit(EnrollmentEditSubmitEvent $event)
    {
        $this->submitEnrollmentEditForm($event, self::PLUGIN_NAME);
    }

    public function onUIForm(SubmittedFormTemplateEvent $event)
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
        $event->getEnrollment()->getPluginData()->add(self::PLUGIN_NAME,['totalPrice' => $totalPrice]);
    }

    public function onUISuccess(EnrollmentTemplateEvent $event)
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

<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Form;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use Doctrine\ORM\EntityManager;
use PluginBundle\Entity\ShortUrl;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ShortUrlPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'short_url';
    use PluginConfigurationHelperTrait;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * ShortUrlPluginListener constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => ['onPluginBuildForm', 20],
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => ['onAdminShowForm', 20],
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $shortUrl = $event->isNew()?null:$this->getShortUrl($event->getForm());
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('slug', TextType::class, [
                'label' => 'plugin.short_url.conf.slug',
                'data' => $shortUrl?$shortUrl->getSlug():null,
                'constraints' => [
                    new Regex('/[a-z0-9-]+/'),
                    new Callback(function ($slug, ExecutionContextInterface $context) use($shortUrl) {
                        $existingShortUrl = $this->em->getRepository(ShortUrl::class)->findOneBy(['slug' => $slug]);
                        if($existingShortUrl !== null && $existingShortUrl !== $shortUrl)
                            $context->buildViolation('This value is already used.')
                                ->setInvalidValue($slug)
                                ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
                                ->addViolation();
                    }),
                ]
            ])
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $shortUrl = $this->getShortUrl($event->getForm());
        $this->submitPluginForm($event, self::PLUGIN_NAME);
        if($event->getType() === PluginSubmitFormEvent::TYPE_DELETE) {
            // Remove the shorturl and be done with it.
            $this->em->remove($shortUrl);
            $this->em->flush();
            return;
        }
        if(!$shortUrl) {
            $shortUrl = new ShortUrl();
            $shortUrl->setForm($event->getForm());
            $this->em->persist($shortUrl);
        }
        if($event->getForm()->getPluginData()->has(self::PLUGIN_NAME)) {
            $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
            $shortUrl->setSlug($pluginData['slug']);
            unset($pluginData['slug']);
            $event->getForm()->getPluginData()->set(self::PLUGIN_NAME, $pluginData);
        }
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'ShortUrlPlugin', 'Admin/get', 'html', 'twig'), [
            'shorturl' => $this->getShortUrl($event->getForm()),
        ]);
    }

    /**
     * @param Form $form
     * @return null|ShortUrl
     */
    private function getShortUrl(Form $form)
    {
        return $this->em->getRepository(ShortUrl::class)->findOneBy(['form' => $form]);
    }

}

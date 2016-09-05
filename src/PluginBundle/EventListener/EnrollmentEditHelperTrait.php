<?php

namespace PluginBundle\EventListener;

use AdamQuaile\Bundle\FieldsetBundle\Form\FieldsetType;
use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentEditSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FormType;

trait EnrollmentEditHelperTrait
{
    /**
     * Creates a basic plugin enrollment settings form
     * @param EnrollmentEditEvent $event
     * @param string $name The name of the plugin
     * @return \Symfony\Component\Form\FormBuilderInterface A formbuilder to add all plugin enrollment options to
     */
    private function buildEnrollmentEditForm(EnrollmentEditEvent $event, $name)
    {
        return $event->getFormBuilder()
            ->add($name, FieldsetType::class, [
                'legend' => 'plugin.'.$name.'.title',
                'label' => false,
            ])
            ->get($name)
            ->add('form', FormType::class, [
                'label' => false,
                'data' => $event->getEnrollment()->getPluginData()->get($name),
                'attr' => [
                    'style' => 'horizontal',
                ],
            ])
            ->get('form')
            ;
    }

    /**
     * Handles updating of plugin enrollment data on plugin enrollment settings form submission
     * @param EnrollmentEditSubmitEvent $event
     * @param string $name
     */
    private function submitEnrollmentEditForm(EnrollmentEditSubmitEvent $event, $name)
    {
        $submittedEnrollmentData = $event->getSubmittedForm()
            ->get($name)
            ->get('form')
            ->getData();

        $event->getEnrollment()->getPluginData()->add($name, $submittedEnrollmentData);
    }
}

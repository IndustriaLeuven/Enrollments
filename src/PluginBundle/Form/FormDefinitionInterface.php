<?php
/**
 * Created by PhpStorm.
 * User: Lars
 * Date: 1/02/2016
 * Time: 19:59
 */
namespace PluginBundle\Form;

use AppBundle\Entity\Enrollment;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

interface FormDefinitionInterface
{
    /**
     * Builds the form
     *
     * @param FormBuilderInterface $formBuilder
     * @return void
     */
    public function buildForm(FormBuilderInterface $formBuilder);

    /**
     * Adjusts form data after submission
     * @param Form $form
     * @return void
     */
    public function handleSubmission(Form $form, Enrollment $enrollment);

    /**
     * @see buildForm()
     * @param FormBuilderInterface $formBuilder
     * @return void
     */
    public function __invoke(FormBuilderInterface $formBuilder);
}

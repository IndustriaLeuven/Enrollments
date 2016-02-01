<?php

namespace PluginBundle\Form;

use AppBundle\Entity\Enrollment;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class FormDefinition implements FormDefinitionInterface
{
    /**
     * @var \Closure
     */
    private $formBuilder;

    /**
     * @var \Closure|null
     */
    private $submissionHandler;

    /**
     * FormDefinition constructor.
     * @param \Closure $formBuilder
     * @param \Closure|null $submissionHandler
     */
    public function __construct(\Closure $formBuilder, \Closure $submissionHandler = null)
    {
        $this->formBuilder = $formBuilder;
        $this->submissionHandler = $submissionHandler;
    }

    public function buildForm(FormBuilderInterface $formBuilder)
    {
        $closure = $this->formBuilder;
        $closure($formBuilder);
    }

    public function handleSubmission(Form $form, Enrollment $enrollment)
    {
        $closure = $this->submissionHandler;
        if($closure)
            $closure($form, $enrollment);
    }

    function __invoke(FormBuilderInterface $formBuilder)
    {
        $this->buildForm($formBuilder);
    }
}

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
     * @var \Closure
     */
    private $configFormBuilder;

    /**
     * FormDefinition constructor.
     *
     * @param \Closure $formBuilder
     * @param \Closure|null $submissionHandler
     * @param \Closure|null $configFormBuilder
     */
    public function __construct(\Closure $formBuilder, \Closure $submissionHandler = null, \Closure $configFormBuilder = null)
    {
        $this->formBuilder = $formBuilder;
        $this->submissionHandler = $submissionHandler;
        $this->configFormBuilder = $configFormBuilder;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $config = [])
    {
        $closure = $this->formBuilder;
        $closure($formBuilder, $config);
    }

    public function handleSubmission(Form $form, Enrollment $enrollment, array $config = [])
    {
        $closure = $this->submissionHandler;
        if($closure)
            $closure($form, $enrollment, $config);
    }

    public function buildConfigForm(FormBuilderInterface $formBuilder)
    {
        $closure = $this->configFormBuilder;
        if($closure)
            $closure($formBuilder);
    }
}

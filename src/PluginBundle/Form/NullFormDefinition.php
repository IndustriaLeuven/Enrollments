<?php

namespace PluginBundle\Form;

use AppBundle\Entity\Enrollment;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormStaticControlType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class NullFormDefinition implements FormDefinitionInterface
{
    /**
     * @var \Error|\Exception|null
     */
    private $exception;

    /**
     * NullFormDefinition constructor.
     *
     * @param \Exception|\Error|null $exception
     */
    public function __construct($exception = null)
    {
        $this->exception = $exception;
    }

    private function addException(FormBuilderInterface $formBuilder) {
        if($this->exception)
            $formBuilder->add('_error', FormStaticControlType::class, [
                'label' => 'ERROR',
                'attr' => [
                    'class' => 'text-danger'
                ],
                'data' => $this->exception->getMessage(),
            ]);
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $config = [])
    {
        $this->addException($formBuilder);
    }

    public function handleSubmission(Form $form, Enrollment $enrollment, array $config = [])
    {
    }

    public function buildConfigForm(FormBuilderInterface $formBuilder)
    {
        $this->addException($formBuilder);
    }
}

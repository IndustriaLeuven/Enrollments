<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SetDataEvent;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\UI\FormTemplateEvent;
use AppBundle\Event\UI\SuccessTemplateEvent;
use AppBundle\Event\UIEvents;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @View("AppBundle:Enrollment:simpleTemplate.html.twig")
 */
class EnrollmentController extends BaseController implements ClassResourceInterface
{
    public function getAction(Form $form)
    {
        return $this->getEventDispatcher()->dispatch(UIEvents::FORM, new FormTemplateEvent($form));
    }

    public function postAction(Request $request, Form $form)
    {
        $formTemplateEvent = new FormTemplateEvent($form);
        $this->getEventDispatcher()->dispatch(UIEvents::FORM, $formTemplateEvent);

        $submittedForm = $formTemplateEvent->getSubmittedForm();

        $submittedForm->handleRequest($request);

        if($submittedForm->isValid()) {
            $enrollment = new Enrollment($form);
            $enrollment->setData($submittedForm->getData());
            $submitFormEvent = new SubmitFormEvent($form, $submittedForm, $enrollment);
            $this->getEventDispatcher()->dispatch(FormEvents::SUBMIT, $submitFormEvent);
            if(!$submittedForm->getErrors(true)->count()) {
                $this->getEntityManager()->persist($enrollment);
                $this->getEntityManager()->flush();
                return $this->redirectToRoute('app_get_enrollment_submission', [
                    'form' => $form->getId(),
                    'enrollment' => $enrollment->getId(),
                ]);
            }
        }

        return $formTemplateEvent;
    }

    public function getSubmissionAction(Form $form, Enrollment $enrollment)
    {
        return $this->getEventDispatcher()->dispatch(UIEvents::SUCCESS, new SuccessTemplateEvent($form, $enrollment));
    }
}

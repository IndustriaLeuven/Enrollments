<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Form;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @View
 */
class FormController extends BaseController implements ClassResourceInterface
{
    public function cgetAction(Request $request)
    {
        return $this->paginate($this->getEntityManager()->getRepository('AppBundle:Form')->createQueryBuilder('f'), $request);
    }

    public function getAction(Form $form)
    {
        return ['data' => $this->getEventDispatcher()
            ->dispatch(AdminEvents::FORM_GET, new SubmittedFormTemplateEvent($form))];
    }

    public function newAction()
    {
        return [
            'data' => $this->buildPluginForm()
            ->setMethod('POST')
            ->setAction($this->generateUrl('admin_post_form'))
            ->getForm()
            ->createView()
        ];
    }

    /**
     * @View("AppBundle:Admin/Form:new.html.twig")
     */
    public function postAction(Request $request)
    {
        $submittedForm = $this->buildPluginForm()
            ->setMethod('POST')
            ->setAction($this->generateUrl('admin_post_form'))
            ->getForm();

        $submittedForm->handleRequest($request);
        if($submittedForm->isValid()) {
            $form = new Form();
            $form->setName($submittedForm->get('name')->getData());

            $this->getEventDispatcher()->dispatch(PluginEvents::SUBMIT_FORM, new PluginSubmitFormEvent($submittedForm, $form, PluginSubmitFormEvent::TYPE_NEW));

            $this->getEntityManager()->persist($form);
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('admin_get_form', ['form' => $form->getId()]);
        }
        return ['data' => $submittedForm->createView()];
    }

    public function editAction(Form $form)
    {
        return $this->buildPluginForm($form)
            ->setMethod('PUT')
            ->setAction($this->generateUrl('admin_put_form', ['form' => $form->getId()]))
            ->getForm()
            ->createView();
    }

    /**
     * @View("AppBundle:Admin/Form:edit.html.twig")
     */
    public function putAction(Request $request, Form $form)
    {
        $submittedForm = $this->buildPluginForm($form)
            ->setMethod('PUT')
            ->setAction($this->generateUrl('admin_put_form', ['form' => $form->getId()]))
            ->getForm();

        $submittedForm->handleRequest($request);

        if($submittedForm->isValid()) {
            $form->setName($submittedForm->get('name')->getData());

            $this->getEventDispatcher()->dispatch(PluginEvents::SUBMIT_FORM, new PluginSubmitFormEvent($submittedForm, $form, PluginSubmitFormEvent::TYPE_EDIT));

            $this->getEntityManager()->flush();

            return $this->redirectToRoute('admin_get_form', ['form' => $form->getId()]);
        }

        return $submittedForm->createView();
    }

    public function removeAction(Form $form)
    {
        return $this->createFormBuilder()
            ->add('delete', 'submit', [
                'button_class' => 'danger'
            ])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('admin_delete_form', ['form' => $form->getId()]))
            ->getForm()
            ->createView();
    }

    /**
     * @View("AppBundle:Admin/Form:remove.html.twig")
     */
    public function deleteAction(Request $request, Form $form)
    {
        $submittedForm = $this->createFormBuilder()
            ->add('delete', 'submit', [
                'button_class' => 'danger'
            ])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('admin_delete_form', ['form' => $form->getId()]))
            ->getForm();

        $submittedForm->handleRequest($request);

        if($submittedForm->isValid()) {
            $this->getEventDispatcher()->dispatch(PluginEvents::SUBMIT_FORM, new PluginSubmitFormEvent($submittedForm, $form, PluginSubmitFormEvent::TYPE_DELETE));

            $this->getEntityManager()->remove($form);
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('admin_get_forms');
        }

        return $submittedForm->createView();
    }

    /**
     * @return FormBuilder
     */
    private function buildPluginForm(Form $form = null)
    {
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('name', 'text', ['data' => $form?$form->getName():'']);

        $buildConfigEvent = $this->getEventDispatcher()->dispatch(PluginEvents::BUILD_FORM, new PluginBuildFormEvent($formBuilder, $form));
        /* @var $buildConfigEvent PluginBuildFormEvent */
        return $buildConfigEvent->getFormBuilder()
            ->add('submit', 'submit');
    }

}

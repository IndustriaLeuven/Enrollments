<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Form;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Form\AuthserverGroupsChoiceLoader;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\BootstrapCollectionType;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @View
 */
class FormController extends BaseController implements ClassResourceInterface
{
    public function cgetAction(Request $request)
    {
        $queryBuilder = $this->getEntityManager()
            ->getRepository('AppBundle:Form')
            ->createQueryBuilder('f')
            ->orderBy('f.createdAt', 'DESC')
        ;
        return $this->paginate($queryBuilder, $request);
    }

    public function getAction(Form $form)
    {
        return ['data' => $this->getEventDispatcher()
            ->dispatch(AdminEvents::FORM_GET, new SubmittedFormTemplateEvent($form))];
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $form = null;
        if($request->query->has('copy_from')) {
            $form = $this->getEntityManager()->find('AppBundle:Form', $request->query->get('copy_from'));
        }
        return [
            'data' => $this->buildPluginForm($form)
            ->setMethod('POST')
            ->setAction($this->generateUrl('admin_post_form'))
            ->getForm()
            ->createView()
        ];
    }

    /**
     * @View("AppBundle:Admin/Form:new.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $request)
    {
        $submittedForm = $this->buildPluginForm()
            ->setMethod('POST')
            ->setAction($this->generateUrl('admin_post_form'))
            ->getForm();

        $submittedForm->handleRequest($request);
        if($submittedForm->isValid()) {
            $form = $submittedForm->getData();
            $this->getEventDispatcher()->dispatch(PluginEvents::SUBMIT_FORM, new PluginSubmitFormEvent($submittedForm->get('plugin_data'), $form, PluginSubmitFormEvent::TYPE_NEW));

            $this->getEntityManager()->persist($form);
            $this->getEntityManager()->flush();

            return $this->redirectToRoute('admin_get_form', ['form' => $form->getId()]);
        }
        return ['data' => $submittedForm->createView()];
    }

    /**
     * @Security("is_granted('EDIT', form) or has_role('ROLE_ADMIN')")
     */
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
     * @Security("is_granted('EDIT', form) or has_role('ROLE_ADMIN')")
     */
    public function putAction(Request $request, Form $form)
    {
        $submittedForm = $this->buildPluginForm($form)
            ->setMethod('PUT')
            ->setAction($this->generateUrl('admin_put_form', ['form' => $form->getId()]))
            ->getForm();

        $submittedForm->handleRequest($request);

        if($submittedForm->isValid()) {
            $this->getEventDispatcher()->dispatch(PluginEvents::SUBMIT_FORM, new PluginSubmitFormEvent($submittedForm->get('plugin_data'), $form, PluginSubmitFormEvent::TYPE_EDIT));

            $this->getEntityManager()->flush();

            return $this->redirectToRoute('admin_get_form', ['form' => $form->getId()]);
        }

        return $submittedForm->createView();
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function removeAction(Form $form)
    {
        return $this->createFormBuilder()
            ->add('delete', SubmitType::class, [
                'label' => 'admin.form.delete',
                'button_class' => 'danger'
            ])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('admin_delete_form', ['form' => $form->getId()]))
            ->getForm()
            ->createView();
    }

    /**
     * @View("AppBundle:Admin/Form:remove.html.twig")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Form $form)
    {
        $submittedForm = $this->createFormBuilder()
            ->add('delete', SubmitType::class, [
                'label' => 'admin.form.delete',
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
     * @param Form $form
     * @return FormBuilder
     */
    private function buildPluginForm(Form $form = null)
    {
        $formBuilder = $this->createFormBuilder($form, ['data_class'=>Form::class]);
        $formBuilder->add('name', TextType::class, [
            'label' => 'admin.form.name',
        ]);

        $authserverGroupsLoader = new AuthserverGroupsChoiceLoader($this->get('authserver.client'), ['exportable' => '1']);

        foreach(['editFormGroups', 'listEnrollmentsGroups', 'editEnrollmentsGroups'] as $field)
            $formBuilder->add($field, BootstrapCollectionType::class, [
                'label' => 'admin.form.'.$field,
                'type' => ChoiceType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'options' => [
                    'choice_loader' => $authserverGroupsLoader,
                ],
            ]);
        $formBuilder->add('plugin_data', FormType::class, [
            'mapped' => false,
            'label' => false,
        ]);

        $this->getEventDispatcher()->dispatch(PluginEvents::BUILD_FORM, new PluginBuildFormEvent($formBuilder->get('plugin_data'), $form));
        /* @var $buildConfigEvent PluginBuildFormEvent */
        return $formBuilder->add('submit', SubmitType::class, [
            'label' => 'form.submit',
        ]);
    }

}

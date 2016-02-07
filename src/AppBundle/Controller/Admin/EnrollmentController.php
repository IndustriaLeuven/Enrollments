<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Admin\EnrollmentEditEvent;
use AppBundle\Event\Admin\EnrollmentEditSubmitEvent;
use AppBundle\Event\Admin\EnrollmentEvent;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use AppBundle\Plugin\Table\CallbackTableColumnDefinition;
use AppBundle\Plugin\Table\TableColumnDefinitionInterface;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NoRoute;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;
use League\Csv\Modifier\MapIterator;
use League\Csv\Writer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @View
 * @ParamConverter("form", options={"mapping":{"form":"id"}})
 * @ParamConverter("enrollment", options={"mapping":{"form": "form", "enrollment": "id"}})
 */
class EnrollmentController extends BaseController implements ClassResourceInterface
{
    /**
     * @View("AppBundle:Admin/Enrollment:sidebar.html.twig")
     * @NoRoute
     */
    public function sidebarAction(Form $form, $enrollment = null)
    {
        return $this->getEventDispatcher()
            ->dispatch(AdminEvents::ENROLLMENT_SIDEBAR, new EnrollmentSidebarEvent($form, $enrollment));
    }

    /**
     * Renders a csv of all filtered enrollments for a form
     * @param EnrollmentListEvent $event
     * @param Collection $filteredData
     * @param Form $form
     * @return StreamedResponse
     */
    private function renderCgetCsv(EnrollmentListEvent $event, Collection $filteredData, Form $form)
    {
        $headings = [];
        // Collect headers from fields added by listeners
        foreach($event->getFields('csv') as $name => $field) {
            $headings[$name] = $field;
        }
        // Add headers for all other form fields that have not yet been added
        foreach($filteredData as $enrollment) {
            /* @var $enrollment Enrollment */
            foreach(array_keys($enrollment->getFlattenedData()) as $data_key) {
                if(!isset($headings['data.'.$data_key]))
                    $headings['data.'.$data_key] = new CallbackTableColumnDefinition($data_key, function(array $data) {
                        $enrollment = $data['data'];
                        /* @var $enrollment Enrollment */
                        $flattenedData = $enrollment->getFlattenedData();
                        if(isset($flattenedData[$data['fieldName']]))
                            return $flattenedData[$data['fieldName']];
                        return '';
                    }, ['fieldName' => $data_key]);
            }
        }

        // Create csv writer and insert headings
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->insertOne(array_map(function(TableColumnDefinitionInterface $columnDefinition) {
            return $columnDefinition->getColumnHeader();
        }, $headings));

        // Create data rows
        $renderedRows = new MapIterator(new \IteratorIterator($filteredData->getIterator()), function(Enrollment $enrollment) use($headings) {
            $rowData = [];
            $data = ['data' => $enrollment];
            // Add data for each heading
            foreach($headings as $columnDefinition) {
                /* @var $columnDefinition TableColumnDefinitionInterface */
                $rowData[] = html_entity_decode($columnDefinition->renderColumnData($data));
            }
            return $rowData;
        });

        $csvWriter->insertAll($renderedRows);

        $response = StreamedResponse::create(function() use($csvWriter) {
            $csvWriter->output();
        });

        $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $form->getName() . '.csv');
        $response->headers->set('Content-Disposition', $contentDisposition);

        return $response;
    }

    public function cgetAction(Request $request, Form $form)
    {
        $event = new EnrollmentListEvent($form, $request->query, $this->get('twig'));
        $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_LIST, $event);

        $filteredData = $event->getForm()->getEnrollments()
            ->matching($event->getCriteria())
            ->filter($event->getFilter());


        if($request->attributes->get('_format') === 'csv') {
            return $this->renderCgetCsv($event, $filteredData, $form);
        }

        return [
            'data' => $this->paginate($filteredData, $request),
            'event' => $event,
        ];

    }

    public function getAction(Form $form, Enrollment $enrollment)
    {
        return $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_GET, new EnrollmentTemplateEvent($form, $enrollment));
    }

    /**
     * @Get
     * @Post(name="post_edit_form_enrollment")
     */
    public function editAction(Request $request, Form $form, Enrollment $enrollment)
    {
        $userFormEvent = $this->createEnrollmentTemplateEvent($form, $enrollment);
        $adminForm = $this->createEditForm($form, $enrollment);

        $userForm = $userFormEvent->getSubmittedForm();
        $userForm->handleRequest($request);

        if($userForm->isValid()) {
            $enrollment->setData($userForm->getData());
            $submitFormEvent = new SubmitFormEvent($form, $userForm, $enrollment, SubmitFormEvent::TYPE_EDIT);
            $this->getEventDispatcher()->dispatch(FormEvents::SUBMIT, $submitFormEvent);
            if(!$userForm->getErrors(true)->count()) {
                $this->getEntityManager()->flush();
                return $this->redirectToRoute('admin_get_form_enrollment', [
                    'form' => $form->getId(),
                    'enrollment' => $enrollment->getId(),
                ]);
            }
        }

        return [
            'userFormEvent' => $userFormEvent,
            'adminForm' => $adminForm->createView(),
        ];
    }

    /**
     * @View("AppBundle:Admin/Enrollment:edit.html.twig")
     */
    public function putAction(Request $request, Form $form, Enrollment $enrollment)
    {
        $userFormEvent = $this->createEnrollmentTemplateEvent($form, $enrollment);
        $adminForm = $this->createEditForm($form, $enrollment);

        $adminForm->handleRequest($request);

        if($adminForm->isValid()) {
            $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_EDIT_SUBMIT, new EnrollmentEditSubmitEvent($form, $enrollment, $adminForm));
            $this->getEntityManager()->flush();
            return $this->redirectToRoute('admin_get_form_enrollment', [
                'form' => $form->getId(),
                'enrollment' => $enrollment->getId()
            ]);
        }

        return [
            'userFormEvent' => $userFormEvent,
            'adminForm' => $adminForm->createView(),
        ];
    }

    public function removeAction(Form $form, Enrollment $enrollment)
    {
        return $this->createFormBuilder()
            ->add('delete', SubmitType::class, [
                'button_class' => 'danger'
            ])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('admin_delete_form_enrollment', ['form' => $form->getId(), 'enrollment' => $enrollment->getId()]))
            ->getForm()
            ->createView();
    }

    /**
     * @View("AppBundle:Admin/Enrollment:remove.html.twig")
     */
    public function deleteAction(Request $request, Form $form, Enrollment $enrollment)
    {
        $deleteForm = $this->createFormBuilder()
            ->add('delete', SubmitType::class, [
                'button_class' => 'danger'
            ])
            ->setMethod('DELETE')
            ->setAction($this->generateUrl('admin_delete_form_enrollment', ['form' => $form->getId(), 'enrollment' => $enrollment->getId()]))
            ->getForm();
        /* @var $deleteForm \Symfony\Component\Form\Form */
        $deleteForm->handleRequest($request);
        if($deleteForm->isValid()) {
            $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_DELETE, new EnrollmentEvent($form, $enrollment));
            $this->getEntityManager()->flush();
            return $this->redirectToRoute('admin_get_form_enrollments', [
                'form' => $form->getId()
            ]);
        }

        return $deleteForm->createView();

    }

    /**
     * @return FormFactoryInterface
     */
    private function getFormFactory()
    {
        return $this->get('form.factory');
    }

    /**
     * @param Form $form
     * @param Enrollment $enrollment
     * @return \Symfony\Component\Form\Form
     */
    private function createEditForm(Form $form, Enrollment $enrollment)
    {
        $adminFormEvent = new EnrollmentEditEvent($form, $enrollment, $this->getFormFactory()->createNamedBuilder('admin'));
        $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_EDIT, $adminFormEvent);

        $adminForm = $adminFormEvent->getFormBuilder()
            ->setMethod('PUT')
            ->setAction($this->generateUrl('admin_put_form_enrollment', ['form' => $form->getId(), 'enrollment' => $enrollment->getId()]))
            ->getForm();
        return $adminForm;
    }

    /**
     * @param Form $form
     * @param Enrollment $enrollment
     * @return EnrollmentTemplateEvent
     */
    private function createEnrollmentTemplateEvent(Form $form, Enrollment $enrollment)
    {
        $userFormEvent = new EnrollmentTemplateEvent($form, $enrollment, false);
        $this->getEventDispatcher()->dispatch(UIEvents::SUCCESS, $userFormEvent);
        return $userFormEvent;
    }
}

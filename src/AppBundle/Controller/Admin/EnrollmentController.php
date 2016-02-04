<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseController;
use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Admin\EnrollmentListEvent;
use AppBundle\Event\Admin\EnrollmentSidebarEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use AppBundle\Plugin\TableColumnDefinition;
use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Controller\Annotations\NoRoute;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Routing\ClassResourceInterface;
use League\Csv\Modifier\MapIterator;
use League\Csv\Writer;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @View
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
                    $headings['data.'.$data_key] = new TableColumnDefinition($data_key, new TemplateReference('AppBundle', 'Admin/Enrollment/list', 'flattenedDataField', 'csv', 'twig'), [
                        'fieldName' => $data_key,
                    ]);
            }
        }

        $twig = $this->get('twig');
        /* @var $twig \Twig_Environment */

        // Create csv writer and insert headings
        $csvWriter = Writer::createFromFileObject(new \SplTempFileObject());
        $csvWriter->insertOne(array_map(function(TableColumnDefinition $columnDefinition) {
            return $columnDefinition->getFriendlyName();
        }, $headings));

        // Create data rows
        $renderedRows = new MapIterator(new \IteratorIterator($filteredData->getIterator()), function(Enrollment $enrollment) use($headings, $twig) {
            $rowData = [];
            $data = ['data' => $enrollment];
            // Add data for each heading
            foreach($headings as $columnDefinition) {
                /* @var $columnDefinition TableColumnDefinition */
                // First try a .csv.twig template before falling back to the .html version
                try {
                    $csvTemplate = clone $columnDefinition->getTemplate();
                    $csvTemplate->set('format', 'csv');
                    $renderedData = $twig->render($csvTemplate, $columnDefinition->getExtraData() + $data);
                } catch(\Twig_Error_Loader $_) {
                    $renderedData = $twig->render($columnDefinition->getTemplate(), $columnDefinition->getExtraData() + $data);
                }
                // Decode html and strip leading and tailing whitespace
                $rowData[] = trim(html_entity_decode($renderedData));
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
        $event = new EnrollmentListEvent($form, $request->query);
        $this->getEventDispatcher()->dispatch(AdminEvents::ENROLLMENT_LIST, $event);

        $filteredData = $event->getForm()->getEnrollments()->matching($event->getCriteria());

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
        return $this->getEventDispatcher()->dispatch(UIEvents::SUCCESS, new EnrollmentTemplateEvent($form, $enrollment));
    }

    public function editAction(Form $form, Enrollment $enrollment)
    {
        throw new HttpException(501, 'Enrollment edit not implemented.');

    }

    public function removeAction(Form $form, Enrollment $enrollment)
    {
        throw new HttpException(501, 'Enrollment deletion not implemented.');
    }
}

<?php

namespace PluginBundle\Controller;

use AppBundle\Entity\Enrollment;
use AppBundle\Util;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use PluginBundle\Event\AdmissionCheckEvent;
use PluginBundle\EventListener\AdmissionCheckPluginListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdmissionCheckPluginController extends Controller
{
    /**
     * @Get("/g/{enrollment}/{signature}")
     * @Security("has_role('ROLE_BACKEND_ACCESS')")
     * @View
     * @param $enrollment
     * @param $signature
     */
    public function checkAction($enrollment, $signature)
    {
        $admissionEvent = new AdmissionCheckEvent();
        $admissionEvent->addReason(AdmissionCheckEvent::VALIDITY_ABSTAIN, AdmissionCheckPluginListener::PLUGIN_NAME, 'plugin.admission_check.reason.abstain');
        $enrollmentUuid = Util::shortuuid_decode($enrollment);
        if(Util::base64_decode_urlsafe($signature) !== hash_hmac('sha256', $enrollmentUuid, $this->container->getParameter('urlsign_key'), true)) {
            $admissionEvent->addReasonedVote(AdmissionCheckEvent::VALIDITY_DENY, AdmissionCheckPluginListener::PLUGIN_NAME, 'plugin.admission_check.reason.bad_signature');
            return $admissionEvent;
        }
        $em = $this->getDoctrine()->getManagerForClass(Enrollment::class);
        $enrollment = $em->find(Enrollment::class, $enrollmentUuid);
        /* @var $enrollment Enrollment */
        if(!$enrollment) {
            $admissionEvent->addReasonedVote(AdmissionCheckEvent::VALIDITY_DENY, AdmissionCheckPluginListener::PLUGIN_NAME, 'plugin.admission_check.reason.no_enrollment');
            return $admissionEvent;
        }

        if(!$this->isGranted('EDIT_ENROLLMENT', $enrollment->getForm())) {
            throw $this->createAccessDeniedException('You do not have edit permissions on this form.');
        }

        $admissionEvent->setEnrollment($enrollment);
        $this->get('event_dispatcher')->dispatch(AdmissionCheckEvent::EVENT_NAME, $admissionEvent);

        if($admissionEvent->isValid()) {
            $enrollment->getPluginData()->add(AdmissionCheckPluginListener::PLUGIN_NAME, ['used' => true]);
            $em->flush();
        }

        return $admissionEvent;
    }
}

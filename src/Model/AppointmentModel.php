<?php


namespace Gems\Api\Fhir\Model;


use Gems\Agenda\Agenda;
use Gems\Api\Fhir\Model\Transformer\AppointmentParticipantTransformer;
use Gems\Api\Fhir\Model\Transformer\AppointmentServiceTypeTransformer;
use Gems\Api\Fhir\Model\Transformer\AppointmentStatusTransformer;
use Gems\Api\Fhir\Model\Transformer\IntTransformer;
use Gems\Model\JoinModel;
use MUtil\Translate\Translator;

class AppointmentModel extends JoinModel
{
    public function __construct(protected Agenda $agenda, Translator $translator)
    {
        $this->translate = $translator;
        parent::__construct('appointments', 'gems__appointments', 'gap');

        $this->addTable(
            'gems__respondent2org',
            array('gap_id_user' => 'gr2o_id_user', 'gap_id_organization' => 'gr2o_id_organization'),
            'gr2o',
            false
        );

        $this->addColumn(new \Zend_Db_Expr("'appointment'"), \Gems\Model::ID_TYPE);
        $this->setKeys(array(\Gems\Model::APPOINTMENT_ID => 'gap_id_appointment'));

        $codes = $this->agenda->getStatusCodesInactive();
        if (isset($codes['CA'])) {
            $cancelCode = 'CA';
        } elseif ($codes) {
            reset($codes);
            $cancelCode = key($codes);
        } else {
            $cancelCode = null;
        }
        if ($cancelCode) {
            $this->setDeleteValues('gap_status', $cancelCode);
        }



        $this->addTable('gems__respondents', ['grs_id_user' =>  'gap_id_user'], 'grs');
        $this->addTable('gems__organizations', ['gap_id_organization' => 'gor_id_organization'],'gor');
        $this->addLeftTable('gems__agenda_activities', ['gap_id_activity' =>  'gaa_id_activity'], 'gaa');
        $this->addLeftTable('gems__agenda_staff', ['gap_id_attended_by' =>  'gas_id_staff'], 'gas');
        $this->addLeftTable('gems__locations', ['gap_id_location' =>  'glo_id_location'], 'glo');

        $this->addColumn('gap_admission_time', 'admission_date');
        $this->addColumn(new \Zend_Db_Expr('\'Appointment\''), 'resourceType');

        $this->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $this->set('gap_id_appointment', [
            'label' => 'id',
            'apiName' => 'id'
        ]);
        $this->set('gap_status', [
            'label' => 'active',
            'apiName', 'status',
        ]);
        $this->set('gap_admission_time', [
            'label' => 'start',
            'apiName', 'start',
        ]);
        // Search options
        $this->set('admission_date', [
            'label' => 'date',
            'apiName', 'date',
        ]);

        $this->set('gap_discharge_time', [
            'label' => 'end',
            'apiName', 'end',
        ]);
        $this->set('gap_created', [
            'label' => 'created',
            'apiName', 'created',
        ]);
        $this->set('gap_subject', [
            'label' => 'comment',
            'apiName', 'comment',
        ]);
        $this->set('gap_comment', [
            'label' => 'description',
            'apiName', 'description',
        ]);

        $this->set('serviceType', [
            'label' => 'serviceType'
        ]);

        $this->set('gap_created', [
            'label' => 'created',
            'apiName', 'created',
        ]);
        $this->set('gap_changed', [
            'label' => 'changed',
            'apiName', 'changed',
        ]);

        // Search options
        $this->set('service-type', [
            'label' => 'service-type',
        ]);
        $this->set('service-type.display', [
            'label' => 'service-type.display',
        ]);

        $this->set('participant', [
            'label' => 'participant',
        ]);
        // Search options
        $this->set('patient', [
            'label' => 'patient',
        ]);
        $this->set('patient.email', [
            'label' => 'patient.email',
        ]);
        $this->set('practitioner', [
            'label' => 'practitioner',
        ]);
        $this->set('practitioner.name', [
            'label' => 'practitioner.name',
        ]);
        $this->set('location', [
            'label' => 'location',
        ]);
        $this->set('location.name', [
            'label' => 'location.name',
        ]);

        $this->addTransformer(new AppointmentStatusTransformer());
        $this->addTransformer(new AppointmentServiceTypeTransformer());
        $this->addTransformer(new AppointmentParticipantTransformer());
        $this->addTransformer(new IntTransformer(['gap_id_appointment']));
    }
}

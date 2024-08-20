<?php


namespace Gems\Api\Fhir\Model;


use Gems\Agenda\Agenda;
use Gems\Api\Fhir\Model\Transformer\AppointmentParticipantTransformer;
use Gems\Api\Fhir\Model\Transformer\AppointmentServiceTypeTransformer;
use Gems\Api\Fhir\Model\Transformer\AppointmentStatusTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class AppointmentModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Agenda $agenda,
        MaskRepository $maskRepository,
    ) {
        parent::__construct('gems__appointments', $metaModelLoader, $sqlRunner, $translate, 'appointments');

        $metaModel = $this->getMetaModel();

        $this->addTable(
            'gems__respondent2org',
            ['gap_id_user' => 'gr2o_id_user', 'gap_id_organization' => 'gr2o_id_organization'],
        );

        $this->addColumn(new Expression('\'appointment\''), \Gems\Model::ID_TYPE);
        $metaModel->setKeys([\Gems\Model::APPOINTMENT_ID => 'gap_id_appointment']);

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
            //$metaModel->setDeleteValues('gap_status', $cancelCode);
        }

        $this->addTable('gems__respondents', ['grs_id_user' =>  'gap_id_user']);
        $this->addTable('gems__organizations', ['gap_id_organization' => 'gor_id_organization']);
        $this->addLeftTable('gems__agenda_activities', ['gap_id_activity' =>  'gaa_id_activity']);
        $this->addLeftTable('gems__agenda_staff', ['gap_id_attended_by' =>  'gas_id_staff']);
        $this->addLeftTable('gems__locations', ['gap_id_location' =>  'glo_id_location']);

        $this->addColumn('gap_admission_time', 'admission_date');
        $this->addColumn(new Expression('\'Appointment\''), 'resourceType');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $metaModel->set('gap_id_appointment', [
            'label' => 'id',
            'apiName' => 'id'
        ]);
        $metaModel->set('gap_status', [
            'label' => 'active',
            'apiName' => 'status',
        ]);
        $metaModel->set('gap_admission_time', [
            'label' => 'start',
            'apiName' => 'start',
        ]);
        // Search options
        $metaModel->set('admission_date', [
            'label' => 'date',
            'apiName' => 'date',
        ]);

        $metaModel->set('gap_discharge_time', [
            'label' => 'end',
            'apiName' => 'end',
        ]);
        $metaModel->set('gap_created', [
            'label' => 'created',
            'apiName' => 'created',
        ]);
        $metaModel->set('gap_subject', [
            'label' => 'comment',
            'apiName' => 'comment',
        ]);
        $metaModel->set('gap_comment', [
            'label' => 'description',
            'apiName' => 'description',
        ]);

        $metaModel->set('serviceType', [
            'label' => 'serviceType'
        ]);

        $metaModel->set('gap_created', [
            'label' => 'created',
            'apiName' => 'created',
        ]);
        $metaModel->set('gap_changed', [
            'label' => 'changed',
            'apiName' => 'changed',
        ]);

        // Search options
        $metaModel->set('service-type', [
            'label' => 'service-type',
        ]);
        $metaModel->set('service-type.display', [
            'label' => 'service-type.display',
        ]);

        $metaModel->set('participant', [
            'label' => 'participant',
        ]);
        // Search options
        $metaModel->set('patient', [
            'label' => 'patient',
        ]);
        $metaModel->set('patient.email', [
            'label' => 'patient.email',
        ]);
        $metaModel->set('practitioner', [
            'label' => 'practitioner',
        ]);
        $metaModel->set('practitioner.name', [
            'label' => 'practitioner.name',
        ]);
        $metaModel->set('location', [
            'label' => 'location',
        ]);
        $metaModel->set('location.name', [
            'label' => 'location.name',
        ]);

        $this->addTransformers();

        $maskRepository->applyMaskToDataModel($metaModel, false, true);
    }

    protected function addTransformers(): void
    {
        $this->metaModel->addTransformer(new AppointmentStatusTransformer());
        $this->metaModel->addTransformer(new AppointmentServiceTypeTransformer());
        $this->metaModel->addTransformer(new AppointmentParticipantTransformer());
    }
}

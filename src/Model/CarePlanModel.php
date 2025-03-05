<?php

namespace Gems\Api\Fhir\Model;


use Gems\Agenda\Repository\AgendaStaffRepository;
use Gems\Agenda\Repository\LocationRepository;
use Gems\Api\Fhir\Model\Transformer\CarePlanActityTransformer;
use Gems\Api\Fhir\Model\Transformer\CarePlanContributorTransformer;
use Gems\Api\Fhir\Model\Transformer\CarePlanInfoTransformer;
use Gems\Api\Fhir\Model\Transformer\CarePlanPeriodTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\RespondentTrackFieldDataModel;
use Gems\Model\Transform\MaskTransformer;
use Gems\Repository\StaffRepository;
use Gems\Tracker;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class CarePlanModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Tracker $tracker,
        MaskRepository $maskRepository,
        protected readonly StaffRepository $staffRepository,
        protected readonly RespondentTrackFieldDataModel $respondentTrackFieldDataModel,
        protected readonly AgendaStaffRepository $agendaStaffRepository,
        protected readonly LocationRepository $locationRepository,
    )
    {
        parent::__construct('gems__respondent2track', $metaModelLoader, $sqlRunner, $translate, 'carePlan');
        $metaModel = $this->getMetaModel();

        $this->addTable(
            'gems__respondents',
            [
                'gr2t_id_user' => 'grs_id_user',
            ],
        );
        $this->addTable(
            'gems__respondent2org',
            [
                'gr2t_id_user' => 'gr2o_id_user',
                'gr2t_id_organization' => 'gr2o_id_organization'
            ],
        );
        $this->addTable(
            'gems__tracks',
            [
                'gr2t_id_track' => 'gtr_id_track',
            ],
        );
        $this->addTable(
            'gems__organizations',
            [
                'gr2t_id_organization' => 'gor_id_organization',
            ],
        );
        $this->addTable(
            'gems__reception_codes',
            [
                'gr2t_reception_code' => 'grc_id_reception_code',
            ],
        );

        $this->addColumn(new Expression('\'CarePlan\''), 'resourceType');
        $this->addColumn(new Expression('\'intent\''), 'order');
        $this->addColumn(
            new Expression(
                '
CASE 
    WHEN grc_success = 1 THEN \'active\' 
    WHEN gr2t_reception_code = \'retract\' THEN \'revoked\' 
    ELSE \'unknown\' 
END'
            ),
            'status'
        );

        $metaModel->set('resourceType', [
            'label' => 'resourceType'
        ]);
        $metaModel->set('gr2t_id_respondent_track', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('intent', [
            'label' => 'intent',
        ]);
        $metaModel->set('status', [
            'label' => 'status',
        ]);
        $metaModel->set('period', [
            'label' => 'period',
        ]);
        $metaModel->set('gtr_track_name', [
            'label' => 'title',
            'apiName' => 'title',
        ]);
        $metaModel->set('gr2t_track_info', [
            'label' => 'description',
            'apiName' => 'description',
        ]);
        $metaModel->set('gtr_code', [
            'label' => 'code',
            'apiName' => 'code',
        ]);
        $metaModel->set('gr2t_created', [
            'label' => 'created',
            'apiName' => 'created',
        ]);
        $metaModel->set('contributor', [
            'label' => 'contributor',
        ]);
        $metaModel->set('supportingInfo', [
            'label' => 'supportingInfo',
        ]);
        $metaModel->set('activity', [
            'label' => 'activity',
        ]);

        $metaModel->set('gr2t_start_date', [
            'label' => 'start',
            'apiName' => 'start',
        ]);
        $metaModel->set('gr2t_end_date', [
            'label' => 'end',
            'apiName' => 'end',
        ]);

        $metaModel->set('gtr_id_track', [
            'label' => 'trackId',
            'apiName' => 'track_id',
        ]);
        $metaModel->set('gr2t_id_organization', [
            'label' => 'organizationId',
            'apiName' => 'organizationId',
        ]);
        $metaModel->set('patient', [
            'label' => 'patient'
        ]);
        $metaModel->set('patient.email', [
            'label' => 'patient.email'
        ]);
        $metaModel->addTransformer(new MaskTransformer($maskRepository));
        $this->addTransformers();
    }

    public function addTransformers(): void
    {
        $this->metaModel->addTransformer(new PatientReferenceTransformer('subject'));
        $this->metaModel->addTransformer(new CarePlanContributorTransformer($this->staffRepository));
        $this->metaModel->addTransformer(new CarePlanPeriodTransformer());
        $this->metaModel->addTransformer(new CarePlanInfoTransformer(
            $this->respondentTrackFieldDataModel,
            $this->agendaStaffRepository,
            $this->locationRepository
        ));

        $this->metaModel->addTransformer(new CarePlanActityTransformer($this->tracker));
    }
}

<?php

namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskExecutionPeriodTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireReferenceTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskForTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskInfoTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireOwnerTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireTaskStatusTransformer;
use Gems\Db\ResultFetcher;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Mezzio\Helper\UrlHelper;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class QuestionnaireTaskModel extends GemsJoinModel
{

    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        MaskRepository $maskRepository,
    )
    {
        parent::__construct('gems__tokens', $metaModelLoader, $sqlRunner, $translate, 'questionaireTasks');
        $metaModel = $this->getMetaModel();

        $this->addTable(
            'gems__respondent2org',
            ['gr2o_id_user' => 'gto_id_respondent', 'gr2o_id_organization' => 'gto_id_organization']
        );
        $this->addTable('gems__respondent2track', ['gto_id_respondent_track' => 'gr2t_id_respondent_track']);
        $this->addTable('gems__reception_codes', ['gto_reception_code' => 'grc_id_reception_code']);
        $this->addTable('gems__surveys', ['gto_id_survey' => 'gsu_id_survey']);
        $this->addTable('gems__tracks', ['gto_id_track' => 'gtr_id_track']);
        $this->addTable('gems__groups', ['gsu_id_primary_group' => 'ggp_id_group']);
        $this->addTable('gems__organizations', ['gto_id_organization' => 'gor_id_organization']);
        $this->addLeftTable('gems__staff', ['gto_by' => 'gsf_id_user']);
        $this->addLeftTable('gems__agenda_staff', ['gsf_id_user' => 'gas_id_user']);
        $this->addLeftTable(
            'gems__respondent_relations',
            ['gto_id_respondent' => 'grr_id_respondent', 'gto_id_relation' => 'grr_id']
        );


        $this->addColumn(new Expression('\'QuestionnaireTask\''), 'resourceType');
        $this->addColumn(new Expression('\'routine\''), 'priority');
        $this->addColumn(new Expression('\'order\''), 'intent');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);
        $metaModel->set('gto_id_token', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('status', [
            'label' => 'status',
            'apiName' => 'status',
        ]);
        $metaModel->set('gto_completion_time', [
            'label' => 'completedAt',
            'apiName' => 'completedAt',
        ]);
        $metaModel->set('priority', [
            'label' => 'priority',
        ]);
        $metaModel->set('intent', [
            'label' => 'intent',
        ]);
        $metaModel->set('owner', [
            'label' => 'owner',
        ]);
        $metaModel->set('gto_created', [
            'label' => 'authoredOn',
            'apiName' => 'authoredOn',
        ]);
        $metaModel->set('gto_changed', [
            'label' => 'lastModified',
            'apiName' => 'lastModified',
        ]);
        $metaModel->set('executionPeriod', [
            'label' => 'executionPeriod',
        ]);

        $metaModel->set('managingOrganization', [
            'label' => 'managingOrganization',
        ]);
        $metaModel->set('info', [
            'label' => 'info',
        ]);

        $metaModel->set('patient', [
            'label' => 'patient',
        ]);
        $metaModel->set('for', [
            'label' => 'for',
        ]);
        $metaModel->set('owner.name', [
            'label' => 'owner.name',
        ]);
        $metaModel->set('owner_name', [
            'label' => 'owner_name',
        ]);
        $metaModel->set('owner.type', [
            'label' => 'owner.type',
        ]);
        $metaModel->set('owner_type', [
            'label' => 'owner_type',
        ]);
        $metaModel->set('survey', [
            'label' => 'survey',
        ]);
        $metaModel->set('survey_name', [
            'label' => 'survey_name',
        ]);
        $metaModel->set('survey_code', [
            'label' => 'survey_code',
        ]);
        $metaModel->set('questionnaire', [
            'label' => 'questionnaire',
        ]);
        $metaModel->set('questionnaire_name', [
            'label' => 'questionnaire_name',
        ]);
        $metaModel->set('questionnaire_code', [
            'label' => 'questionnaire_code',
        ]);

        $metaModel->set('roundDescription', [
            'label' => 'roundDescription',
        ]);
        $metaModel->set('track', [
            'label' => 'track',
        ]);
        $metaModel->set('track_name', [
            'label' => 'track_name',
        ]);
        $metaModel->set('track_code', [
            'label' => 'track_code',
        ]);
        $metaModel->set('carePlan', [
            'label' => 'carePlan',
        ]);
        $metaModel->set('carePlanSuccess', [
            'label' => 'carePlanSuccess',
        ]);
        $metaModel->set('respondentTrackId', [
            'label' => 'respondentTrackId',
        ]);

        $metaModel->set('gto_round_order', [
            'label' => 'roundOrder',
            'apiName' => 'roundOrder',
        ]);

        $this->addTransformers();

        $maskRepository->applyMaskToDataModel($metaModel, false, true);
    }

    protected function addTransformers(): void
    {
        $this->metaModel->addTransformer(new QuestionnaireTaskStatusTransformer());
        $this->metaModel->addTransformer(new QuestionnaireTaskExecutionPeriodTransformer());
        $this->metaModel->addTransformer(new QuestionnaireOwnerTransformer());
        $this->metaModel->addTransformer(new PatientReferenceTransformer('for'));
        $this->metaModel->addTransformer(new ManagingOrganizationTransformer('gto_id_organization', true));
        $this->metaModel->addTransformer(new QuestionnaireReferenceTransformer('focus'));
    }
}

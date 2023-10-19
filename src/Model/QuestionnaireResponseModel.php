<?php

namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireResponseItemsTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireOwnerTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireResponseStatusTransformer;
use Gems\Locale\Locale;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Transform\MaskTransformer;
use Gems\Tracker;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class QuestionnaireResponseModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Tracker $tracker,
        protected readonly Locale $locale,
        MaskRepository $maskRepository,
    ) {
        parent::__construct('gems__tokens', $metaModelLoader, $sqlRunner, $translate, 'questionnaireResponse');

        $metaModel = $this->getMetaModel();

        $this->addTable('gems__respondent2org', ['gr2o_id_user' => 'gto_id_respondent', 'gr2o_id_organization' => 'gto_id_organization']);
        $this->addTable('gems__reception_codes', ['gto_reception_code' => 'grc_id_reception_code', 'gto_completion_time IS NOT NULL']);
        $this->addTable('gems__surveys', ['gto_id_survey' => 'gsu_id_survey']);
        $this->addTable('gems__groups', ['gsu_id_primary_group' => 'ggp_id_group']);
        $this->addTable('gems__organizations', ['gto_id_organization' => 'gor_id_organization']);
        $this->addLeftTable('gems__staff', ['gto_by' => 'gsf_id_user']);
        $this->addLeftTable('gems__agenda_staff', ['gsf_id_user' => 'gas_id_user']);
        $this->addLeftTable('gems__respondent_relations', ['gto_id_respondent' => 'grr_id_respondent', 'gto_id_relation' => 'grr_id']);

        $this->addColumn(new Expression('\'QuestionnaireResponse\''), 'resourceType');

        $metaModel->addTransformer(new MaskTransformer($maskRepository));

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);
        $metaModel->set('gto_id_token', [
            'label' => 'id',
            'apiName' => 'id',
        ]);

        $metaModel->set('gto_completion_time', [
            'label' => 'authored',
            'apiName' => 'authored',
        ]);

        $metaModel->set('status', [
            'label' => 'status',
        ]);
        $metaModel->set('subject', [
            'label' => 'subject',
        ]);
        $metaModel->set('source', [
            'label' => 'source',
        ]);
        $metaModel->set('author', [
            'label' => 'author',
        ]);
        $metaModel->set('item', [
            'label' => 'item',
        ]);

        $metaModel->addTransformer(new QuestionnaireResponseStatusTransformer());
        $metaModel->addTransformer(new PatientReferenceTransformer('subject'));
        $metaModel->addTransformer(new QuestionnaireOwnerTransformer('source'));
        $metaModel->addTransformer(new ManagingOrganizationTransformer('gto_id_organization', true, 'author'));
        $metaModel->addFilter(['gto_completion_time IS NOT NULL']);

        $metaModel->addTransformer(new QuestionnaireResponseItemsTransformer($this->tracker, $this->locale->getLanguage()));

        $maskRepository->applyMaskToDataModel($metaModel);
    }
}

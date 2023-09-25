<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\QuestionnaireItemsTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireSubjectTypeTransformer;
use Gems\Locale\Locale;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Tracker;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class QuestionnaireModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        protected readonly Tracker $tracker,
        protected readonly Locale $locale,
    ) {
        parent::__construct('gems__surveys', $metaModelLoader, $sqlRunner, $translate, 'questionnaires');
        $metaModel = $this->getMetaModel();

        $this->addColumn(new Expression('\'Questionnaire\''), 'resourceType');

        $this->addTable('gems__groups', ['gsu_id_primary_group' => 'ggp_id_group']);

        $this->addColumn(new Expression("
        CASE
            WHEN gsu_active = 1 THEN 'active'
            WHEN gsu_active = 0 AND gsu_status IS NOT NULL THEN 'draft'
            WHEN gsu_active = 0 AND gsu_status IS NULL THEN 'retired'
            ELSE 'unknown'
        END"), 'status');

        $metaModel->set('gsu_id_survey', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('gsu_survey_name', [
            'label' => 'name',
            'apiName' => 'name',
        ]);
        $metaModel->set('status', [
            'label' => 'status',
        ]);
        $metaModel->set('gsu_changed', [
            'label' => 'date',
            'apiName' => 'date',
        ]);
        $metaModel->set('gsu_survey_description', [
            'label' => 'description',
            'apiName' => 'description',
        ]);

        $metaModel->addTransformer(new QuestionnaireSubjectTypeTransformer());
        $metaModel->addTransformer(new QuestionnaireItemsTransformer($this->tracker, $this->locale->getLanguage()));
    }
}

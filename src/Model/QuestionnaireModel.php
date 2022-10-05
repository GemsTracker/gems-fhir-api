<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\QuestionnaireItemsTransformer;
use Gems\Api\Fhir\Model\Transformer\QuestionnaireSubjectTypeTransformer;
use Gems\Tracker;
use MUtil\Model\JoinModel;

class QuestionnaireModel extends JoinModel
{
    /**
     * @var \Zend_Locale
     */
    public $locale;

    /**
     * @var Tracker
     */
    protected $tracker;

    public function __construct()
    {
        parent::__construct('questionnaires', 'gems__surveys', true);

        $this->addColumn(new \Zend_Db_Expr('\'Questionnaire\''), 'resourceType');

        $this->addTable('gems__groups', ['gsu_id_primary_group' => 'ggp_id_group']);

        $this->addColumn(new \Zend_Db_Expr("
        CASE
            WHEN gsu_active = 1 THEN 'active'
            WHEN gsu_active = 0 AND gsu_status IS NOT NULL THEN 'draft'
            WHEN gsu_active = 0 AND gsu_status IS NULL THEN 'retired'
            ELSE 'unknown'
        END"), 'status');

        $this->set('gsu_id_survey', 'label', 'id', 'apiName', 'id');
        $this->set('gsu_survey_name', 'label', 'name', 'apiName', 'name');
        $this->set('status', 'label', 'status');
        $this->set('gsu_changed', 'label', 'date', 'apiName', 'date');
        $this->set('gsu_survey_description', 'label', 'description', 'apiName', 'description');



    }

    public function afterRegistry()
    {
        $this->addTransformer(new QuestionnaireSubjectTypeTransformer());
        $this->addTransformer(new QuestionnaireItemsTransformer($this->tracker, $this->locale->getLanguage()));
    }
}

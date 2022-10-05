<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use MUtil\Model\DatabaseModelAbstract;
use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireReferenceTransformer extends ModelTransformerAbstract
{
    protected string $dbField;

    protected string $field;

    public function __construct($field = 'questionnaire', $dbField = 'gsu_id_survey')
    {
        $this->dbField = $dbField;
        $this->field = $field;
    }

    public function transformFilter(ModelAbstract $model, array $filter): array
    {
        if (isset($filter['survey'])) {
            $filter[$this->dbField] = $filter['survey'];
            unset($filter['survey']);
        }

        if (isset($filter[$this->field])) {
            $filter[$this->dbField] = $filter[$this->field];
            unset($filter[$this->field]);
        }

        if (isset($filter['questionnaire'])) {
            $filter[$this->dbField] = $filter['questionnaire'];
            unset($filter['questionnaire']);
        }

        if (isset($filter['survey_name'])) {
            $value = "%".$filter['survey_name'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = 'gsu_survey_name LIKE ' . $value;
            }

            unset($filter['survey_name']);
        }

        if (isset($filter['questionnaire_name'])) {
            $value = '%'.$filter['questionnaire_name'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = 'gsu_survey_name LIKE ' . $value;
            }

            unset($filter['questionnaire_name']);
        }

        if (isset($filter['survey_code'])) {
            $filter['gsu_code'] = $filter['survey_code'];
            unset($filter['survey_code']);
        }
        if (isset($filter['questionnaire_code'])) {
            $filter['gsu_code'] = $filter['questionnaire_code'];
            unset($filter['questionnaire_code']);
        }

        return $filter;
    }

    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            if (isset($row[$this->dbField])) {
                $questionnaireReference = [
                    'id' => $row[$this->dbField],
                    'reference' => Endpoints::QUESTIONNAIRE . $row[$this->dbField],
                ];

                if (isset($row['gsu_survey_name'])) {
                    $questionnaireReference['display'] = $row['gsu_survey_name'];
                }
                if (isset($row['gsu_code'])) {
                    $questionnaireReference['code'] = $row['gsu_code'];
                }

                $data[$key][$this->field] = $questionnaireReference;
            }
        }
        return $data;
    }
}

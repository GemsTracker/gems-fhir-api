<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use MUtil\Model\DatabaseModelAbstract;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class QuestionnaireReferenceTransformer extends ModelTransformerAbstract
{

    public function __construct(protected string $field = 'questionnaire', protected string $dbField = 'gsu_id_survey')
    {}

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
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
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $data
     * @param $new
     * @param $isPostData
     * @return mixed[]
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
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

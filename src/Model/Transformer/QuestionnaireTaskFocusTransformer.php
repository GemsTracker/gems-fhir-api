<?php

namespace Gems\Api\Fhir\Model\Transformer;


use Gems\Api\Fhir\Endpoints;

class QuestionnaireTaskFocusTransformer extends \MUtil_Model_ModelTransformerAbstract
{
    public function transformFilter(\MUtil_Model_ModelAbstract $model, array $filter): array
    {
        if (isset($filter['survey'])) {
            $filter['gto_id_survey'] = $filter['survey'];
            unset($filter['survey']);
        }

        if (isset($filter['survey_name'])) {
            $value = '%'.$filter['survey_name'].'%';
            if ($model instanceof \MUtil_Model_DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = 'gsu_survey_name LIKE ' . $value;
            }

            unset($filter['survey_name']);
        }

        if (isset($filter['survey.code'])) {
            $filter['gsu_code'] = $filter['survey.code'];
        }

        return $filter;
    }

    public function transformLoad(\MUtil_Model_ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            if (isset($row['gto_id_survey'])) {
                $focus = [
                    'id' => $row['gto_id_survey'],
                    'reference' => Endpoints::QUESTIONNAIRE . $row['gto_id_survey'],
                ];

                if (isset($row['gsu_survey_name'])) {
                    $focus['display'] = $row['gsu_survey_name'];
                }
                if (isset($row['gsu_code'])) {
                    $focus['code'] = $row['gsu_code'];
                }

                $data[$key]['focus'] = $focus;
            }
        }
        return $data;
    }
}

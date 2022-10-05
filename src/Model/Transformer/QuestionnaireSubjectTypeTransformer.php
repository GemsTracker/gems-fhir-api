<?php

namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireSubjectTypeTransformer extends ModelTransformerAbstract
{
    public function transformFilter(ModelAbstract $model, array $filter): array
    {
        if (isset($filter['subjectType'])) {
            switch(strtolower($filter['subjectType'])) {
                case 'patient':
                    $filter['ggp_respondent_members'] = 1;
                    break;
                case 'practitioner':
                    $filter['ggp_staff_members'] = 1;
                    break;
            }
        }

        return $filter;
    }

    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            if (isset($row['ggp_respondent_members']) && $row['ggp_respondent_members'] == 1) {
                $data[$key]['subjectType'] = ['Patient'];
            }
            if (isset($row['ggp_staff_members']) && $row['ggp_staff_members'] == 1) {
                $data[$key]['subjectType'] = ['Practitioner'];
            }
        }

        return $data;
    }
}

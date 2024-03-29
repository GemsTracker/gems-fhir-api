<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class QuestionnaireSubjectTypeTransformer extends ModelTransformerAbstract
{
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['subjectType'])) {
            switch(strtolower($filter['subjectType'])) {
                case 'patient':
                    $filter['ggp_member_type'] = 'respondent';
                    break;
                case 'practitioner':
                    $filter['ggp_member_type'] = 'staff';
                    break;
            }
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

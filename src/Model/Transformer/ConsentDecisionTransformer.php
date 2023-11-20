<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class ConsentDecisionTransformer extends ModelTransformerAbstract
{
    public function transformFilter(MetaModelInterface $model, array $filter)
    {
        if (isset($filter['decision'])) {
            if ($filter['decision'] === 'permit') {
                $filter['gco_description'] = 'Yes';
            }
            if ($filter['decision'] === 'deny') {
                $filter['gco_description'] = 'No';
            }
            unset($filter['decision']);
        }
        return $filter;
    }

    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            if ($row['status'] === 'active' && isset($row['gco_description'])) {
                $data[$key]['decision'] = match($row['gco_description']) {
                    'Yes' => 'permit',
                    'No' => 'deny',
                    default => 'deny',
                };
            }
        }

        return $data;
    }
}
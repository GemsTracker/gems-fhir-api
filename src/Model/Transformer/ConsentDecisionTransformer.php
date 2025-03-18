<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class ConsentDecisionTransformer extends ModelTransformerAbstract
{
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
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

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $data
     * @param bool $new
     * @param bool $isPostData
     * @return mixed[]
     */
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
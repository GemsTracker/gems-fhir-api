<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class ConsentCategoryTransformer extends ModelTransformerAbstract
{
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false)
    {
        foreach ($data as $key=>$row) {
            $data[$key]['category'][] = [
                'coding' => [
                    [
                        'system' => 'http://terminology.hl7.org/CodeSystem/consentscope',
                        'code' => 'research',
                    ],
                ],
            ];
        }
        return $data;
    }
}
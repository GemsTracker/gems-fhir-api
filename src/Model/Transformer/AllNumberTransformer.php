<?php

namespace Gems\Api\Fhir\Model\Transformer;


use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class AllNumberTransformer extends ModelTransformerAbstract
{
    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param MetaModelInterface $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            foreach($row as $column=>$value) {
                if (is_numeric($value)) {
                    $data[$key][$column] = +$value;
                }
            }
        }

        return $data;
    }
}

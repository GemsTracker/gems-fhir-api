<?php

namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class RelatedPersonTelecomTransformer extends ModelTransformerAbstract
{
    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param ModelAbstract $model
     * @param array $filter
     * @return array The (optionally changed) filter
     */
    public function transformFilter(ModelAbstract $model, array $filter): array
    {
        if (isset($filter['email'])) {
            $filter['grr_email'] = $filter['email'];

            unset($filter['email']);
        }

        return $filter;
    }

    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach ($data as $key => $item) {
            $elements = [];
            if (isset($item['grr_email'])) {
                $elements[] = ['system' => 'email', 'value' => $item['grr_email']];
            }

            $data[$key]['telecom'] = $elements;
        }

        return $data;
    }
}

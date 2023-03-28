<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class RelatedPersonTelecomTransformer extends ModelTransformerAbstract
{
    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[] The (optionally changed) filter
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
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
     * @param MetaModelInterface $model The parent model
     * @param mixed[] $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return mixed[] Nested array containing (optionally) transformed data
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
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

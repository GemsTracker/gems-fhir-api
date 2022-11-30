<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class PractitionerTelecomTransformer extends ModelTransformerAbstract
{
    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param MetaModelInterface $model
     * @param array $filter
     * @return array The (optionally changed) filter
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['email'])) {
            $filter['gsf_email'] = $filter['email'];

            unset($filter['email']);
        }

        if (isset($filter['phone'])) {
            $filter['gsf_phone_1'] = $filter['phone'];

            unset($filter['phone']);
        }

        return $filter;
    }

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
        foreach ($data as $key => $item) {
            $elements = [];
            if (isset($item['gsf_email'])) {
                $elements[] = ['system' => 'email', 'value' => $item['gsf_email']];
            }

            if (isset($item['gsf_phone_1'])) {
                $elements[] = ['system' => 'phone', 'value' => $item['gsf_phone_1']];
            }

            $data[$key]['telecom'] = $elements;
        }

        return $data;
    }
}

<?php


namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\DatabaseModelAbstract;
use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class PractitionerHumanNameTransformer extends ModelTransformerAbstract
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
        if (isset($filter['name'])) {
            $value = $filter['name'];
            $filter[] = [
                'gas_name' => $value,
                'gsf_first_name' => $value,
                'gsf_last_name' => $value,
                'gsf_surname_prefix' => $value,
            ];

            unset($filter['name']);
        }

        if (isset($filter['family'])) {
            $value = '%'.$filter['family'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = new \Zend_Db_Expr("CONCAT_WS(' ', gsf_surname_prefix, gsf_last_name) LIKE ".$value);
            }

            unset($filter['family']);
        }

        if (isset($filter['given'])) {
            $value = '%'.$filter['given'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gsf_first_name LIKE ".$value;
            }

            unset($filter['given']);
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
        foreach($data as $key=>$item) {
            $name = [];
            if (isset($item['gas_name'])) {
                $name['text'] = $item['gas_name'];
            }

            if (isset($item['gsf_first_name'])) {
                $name['given'] = $item['gsf_first_name'];

            }

            if (isset($item['gsf_last_name'])) {
                $familyName = $item['gsf_last_name'];
                if (isset($item['gsf_surname_prefix'])) {
                    $familyName = $item['gsf_surname_prefix'] . ' ' . $familyName;
                }
                $name['family'] = $familyName;
            }

            $data[$key]['name'][] = $name;
        }

        return $data;
    }
}

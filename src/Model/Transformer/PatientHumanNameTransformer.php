<?php

namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\DatabaseModelAbstract;
use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class PatientHumanNameTransformer extends ModelTransformerAbstract
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
                'grs_first_name' => $value,
                'grs_initials_name' => $value,
                'grs_last_name' => $value,
                'grs_surname_prefix' => $value,
            ];

            unset($filter['name']);
        }

        if (isset($filter['family'])) {
            $value = '%'.$filter['family'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = new \Zend_Db_Expr("CONCAT_WS(' ', grs_surname_prefix, grs_last_name) LIKE ".$value);
            }

            unset($filter['family']);
        }

        if (isset($filter['given'])) {
            $value = '%'.$filter['given'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "(grs_first_name LIKE ".$value.")
                 OR (grs_initials_name LIKE ".$value.")
                ";
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
            $familyName = $item['grs_last_name'];
            if (isset($item['grs_surname_prefix'])) {
                $familyName = $item['grs_surname_prefix'] . ' ' . $familyName;
            }

            $givenName = $item['grs_first_name'];
            if ($givenName === null && isset($item['grs_initials_name'])) {
                $givenName = $item['grs_initials_name'];
            }

            if (empty(trim($givenName))) {
                $givenName = null;
            }

            $data[$key]['name'][] = [
                'family' => $familyName,
                'given' => $givenName,
            ];
        }

        return $data;
    }
}

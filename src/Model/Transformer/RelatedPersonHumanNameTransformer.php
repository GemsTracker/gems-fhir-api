<?php

namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\DatabaseModelAbstract;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class RelatedPersonHumanNameTransformer extends ModelTransformerAbstract
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
        if (isset($filter['name'])) {
            $value = $filter['name'];
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
            }
            $filter[] = "(grr_first_name = '".$value."')
             OR (grr_last_name = '".$value."')
            ";

            unset($filter['name']);
        }

        if (isset($filter['family'])) {
            $value = '%'.$filter['family'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = new \Zend_Db_Expr("grr_last_name LIKE ".$value);
            }


            unset($filter['family']);
        }

        if (isset($filter['given'])) {
            $value = '%'.$filter['given'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "grr_first_name LIKE ".$value;
            }

            unset($filter['given']);
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
        foreach($data as $key=>$item) {
            $name = [];

            if (isset($item['grr_first_name'])) {
                $name['given'] = $item['grr_first_name'];
            }

            if (isset($item['grr_last_name'])) {
                $name['family'] = $item['grr_last_name'];
            }

            if (count($name)) {
                $name['text'] = join(' ', $name);
            }

            $data[$key]['name'][] = $name;
        }

        return $data;
    }
}

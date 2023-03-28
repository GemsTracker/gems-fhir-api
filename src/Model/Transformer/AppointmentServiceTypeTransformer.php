<?php


namespace Gems\Api\Fhir\Model\Transformer;


use MUtil\Model\DatabaseModelAbstract;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class AppointmentServiceTypeTransformer extends ModelTransformerAbstract
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
        if (isset($filter['service-type'])) {
            $value = (int) $filter['service-type'];
            $filter['gap_id_activity'] = $value;

            unset($filter['service-type']);
        }
        if (isset($filter['serviceType'])) {
            $value = (int) $filter['serviceType'];
            $filter['gap_id_activity'] = $value;

            unset($filter['serviceType']);
        }
        if (isset($filter['service-type.display'])) {
            $value = '%'.$filter['service-type.display'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gaa_name LIKE ".$value;
            }

            unset($filter['service-type.display']);
        }
        if (isset($filter['serviceType.display'])) {
            $value = '%'.$filter['serviceType.display'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gaa_name LIKE ".$value;
            }

            unset($filter['serviceType.display']);
        }

        if (($key = array_search('service-type IS NULL', $filter)) !== false) {
            $filter[$key] = 'gaa_id_activity IS NULL';
        }
        if (($key = array_search('serviceType IS NULL', $filter)) !== false) {
            $filter[$key] = 'gaa_id_activity IS NULL';
        }
        if (($key = array_search('service-type IS NOT NULL', $filter)) !== false) {
            $filter[$key] = 'gaa_id_activity IS NOT NULL';
        }
        if (($key = array_search('serviceType IS NOT NULL', $filter)) !== false) {
            $filter[$key] = 'gaa_id_activity IS NOT NULL';
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
            if (isset($item['gap_id_activity'], $item['gaa_name'])) {
                $coding = [
                    'coding' => [
                        'code' => (int)$item['gap_id_activity'],
                        'display' => $item['gaa_name'],
                    ],
                ];
                $data[$key]['serviceType'][] = $coding;
            }
        }

        return $data;
    }
}

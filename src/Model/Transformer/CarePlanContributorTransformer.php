<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use MUtil\Model\DatabaseModelAbstract;
use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class CarePlanContributorTransformer extends ModelTransformerAbstract
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
        // Organization filters
        if (isset($filter['organization'])) {
            $value = (int)str_replace(['Organization/', Endpoints::ORGANIZATION], '', $filter['organization']);
            $filter['gr2t_id_organization'] = $value;

            unset($filter['organization']);
        }

        if (isset($filter['organization.name'])) {
            $value = '%'.$filter['organization.name'].'%';
            if ($model instanceof DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gor_name LIKE " . $value;
            }

            unset($filter['organization.name']);
        }

        if (isset($filter['organization.code'])) {
            $value = $filter['organization.code'];
            $filter['gor_code'] = $value;

            unset($filter['organization.code']);
        }

        return $filter;
    }

    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            $organizationInfo = [
                'id' => (int)$row['gr2t_id_organization'],
                'reference' => Endpoints::ORGANIZATION . $row['gr2t_id_organization'],
                'display' => $row['gor_name'],
            ];


            $data[$key]['contributor'] = [
                $organizationInfo,
            ];
        }

        return $data;
    }
}

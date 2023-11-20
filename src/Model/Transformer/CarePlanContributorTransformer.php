<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Gems\Repository\StaffRepository;
use MUtil\Model\DatabaseModelAbstract;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class CarePlanContributorTransformer extends ModelTransformerAbstract
{
    public function __construct(
        protected readonly StaffRepository $staffRepository,
    )
    {}
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

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $data
     * @param $new
     * @param $isPostData
     * @return mixed[]
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            $organizationInfo = [
                'id' => (int)$row['gr2t_id_organization'],
                'reference' => Endpoints::ORGANIZATION . $row['gr2t_id_organization'],
                'display' => $row['gor_name'],
            ];

            $staffMembers = $this->staffRepository->getStaff();
            $assignerName = $staffMembers[$row['gr2t_created_by']] ?? null;

            $assignerInfo = [
                'id' => (int)$row['gr2t_created_by'],
                'type' => 'staff',
                'display' => $assignerName,
            ];

            $data[$key]['contributor'] = [
                $organizationInfo,
                $assignerInfo,
            ];
        }

        return $data;
    }
}

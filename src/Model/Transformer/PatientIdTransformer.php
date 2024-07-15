<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class PatientIdTransformer extends ModelTransformerAbstract
{
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['id'])) {
            if (!is_array($filter['id'])) {
                $idParts = explode('@', $filter['id']);
                if (count($idParts) >= 2) {
                    $filter['gr2o_patient_nr'] = $idParts[0];
                    $filter['gr2o_id_organization'] = $this->getAllowedOrganization((int)$idParts[1], $filter);
                }
            } else {
                $multiPatientFilter = [];
                foreach($filter['id'] as $combinedId) {
                    $idParts = explode('@', $combinedId);
                    $patientFilter = [
                        'gr2o_patient_nr' => $idParts[0],
                        'gr2o_id_organization' => $this->getAllowedOrganization((int)$idParts[1], $filter),
                    ];
                    $multiPatientFilter[] = $patientFilter;
                }
                $filter[] = $multiPatientFilter;
            }
            unset($filter['id']);
            if (isset($filter['organization'])) {
                unset($filter['organization']);
            }
        }

        return $filter;
    }

    protected function getAllowedOrganization(int $organizationId, array $filter): int|null
    {
        if (!isset($filter['organization'])) {
            return $organizationId;
        }
        if (in_array($organizationId, $filter['organization'])) {
            return $organizationId;
        }

        return null;
    }
}
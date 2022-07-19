<?php

namespace Gems\Api\Fhir\Model\Transformer;


class PatientIdTransformer extends \MUtil_Model_ModelTransformerAbstract
{
    public function transformFilter(\MUtil_Model_ModelAbstract $model, array $filter): array
    {
        if (isset($filter['id'])) {
            if (!is_array($filter['id'])) {
                $idParts = explode('@', $filter['id']);
                $filter['gr2o_patient_nr'] = $idParts[0];
                $filter['gr2o_id_organization'] = $idParts[1];
            } else {
                $multiPatientFilter = [];
                foreach($filter['id'] as $combinedId) {
                    $idParts = explode('@', $combinedId);
                    $patientFilter['gr2o_patient_nr'] = $idParts[0];
                    $patientFilter['gr2o_id_organization'] = $idParts[1];
                    $multiPatientFilter[] = $patientFilter;
                }
                $filter[] = $multiPatientFilter;
            }
            unset($filter['id']);
        }

        return $filter;
    }
}

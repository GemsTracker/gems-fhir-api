<?php


namespace Gems\Api\Fhir\Model\Transformer;


use Gems\Api\Fhir\Endpoints;
use Gems\Api\Fhir\PatientInformationFormatter;

class AppointmentParticipantTransformer extends \MUtil_Model_ModelTransformerAbstract
{
    /**
     * This transform function checks the filter for
     * a) retrieving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @param array $filter
     * @return array The (optionally changed) filter
     */
    public function transformFilter(\MUtil_Model_ModelAbstract $model, array $filter): array
    {
        if (isset($filter['patient'])) {
            $patientFormatter = new PatientInformationFormatter($filter);
            if (!is_array($filter['patient'])) {
                $filter['patient'] = [$filter['patient']];
            }

            $patientSearchParts = [];
            foreach($filter['patient'] as $patient) {
                $value = explode('@', str_replace(['Patient/', $patientFormatter->getPatientEndpoint()], '', $patient));

                if (count($value) === 2) {
                    $patientSearchParts[] = [
                        'gr2o_patient_nr' => $value[0],
                        'gr2o_id_organization' => $value[1],
                    ];
                }
            }
            if (count($patientSearchParts)) {
                $filter[] = $patientSearchParts;
            }

            unset($filter['patient']);
        }
        if (isset($filter['patient.email'])) {
            $value = $filter['patient.email'];
            unset($filter['patient.email']);

            $filter['gr2o_email'] = $value;
        }

        if (isset($filter['practitioner'])) {
            $value = (int)str_replace(['Practitioner/', Endpoints::PRACTITIONER], '', $filter['practitioner']);
            $filter['gap_id_attended_by'] = $value;

            unset($filter['practitioner']);
        }
        if (isset($filter['practitioner.name'])) {
            $value = '%'.$filter['practitioner.name'].'%';
            if ($model instanceof \MUtil_Model_DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gas_name LIKE ".$value;
            }

            unset($filter['practitioner.name']);
        }

        if (isset($filter['location'])) {
            $value = (int)str_replace(['Location/', Endpoints::LOCATION], '', $filter['location']);
            $filter['gap_id_location'] = $value;

            unset($filter['location']);
        }
        if (isset($filter['location.name'])) {
            $value = $filter['location.name'];
            $filter['glo_name'] = $value;

            unset($filter['location.name']);
        }

        if (isset($filter['organization'])) {
            $value = (int)str_replace(['Organization/', Endpoints::ORGANIZATION], '', $filter['organization']);
            $filter['gap_id_organization'] = $value;

            unset($filter['organization']);
        }
        if (isset($filter['organization.name'])) {
            $value = '%'.$filter['organization.name'].'%';
            if ($model instanceof \MUtil_Model_DatabaseModelAbstract) {
                $adapter = $model->getAdapter();
                $value = $adapter->quote($value);
                $filter[] = "gor_name LIKE ".$value;
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
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param \MUtil_Model_ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(\MUtil_Model_ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$item) {
            if (isset($item['gap_id_user'])) {
                $patientFormatter = new PatientInformationFormatter($item);

                $participant = [
                    'actor' => [
                        'type' => 'Patient',
                        'id' => $patientFormatter->getIdentifier(),
                        'reference' => $patientFormatter->getReference(),
                        'display' => $patientFormatter->getDisplayName(),
                    ],
                ];
                $data[$key]['participant'][] = $participant;
            }
            if (isset($item['gap_id_attended_by'])) {
                $participant = [
                    'actor' => [
                        'type' => 'Practitioner',
                        'id' => $item['gap_id_attended_by'],
                        'reference' => Endpoints::PRACTITIONER . $item['gap_id_attended_by'],
                        'display' => $item['gas_name'],
                    ],
                ];
                $data[$key]['participant'][] = $participant;
            }
            if (isset($item['gap_id_location'])) {
                $participant = [
                    'actor' => [
                        'type' => 'Location',
                        'id' => $item['gap_id_location'],
                        'reference' => Endpoints::LOCATION . $item['gap_id_location'],
                        'display' => $item['glo_name'],
                    ],
                ];
                $data[$key]['participant'][] = $participant;
            }
            if (isset($item['gap_id_organization'])) {
                $participant = [
                    'actor' => [
                        'type' => 'Organization',
                        'id' => $item['gap_id_organization'],
                        'reference' => Endpoints::ORGANIZATION . $item['gap_id_organization'],
                        'display' => $item['gor_name'],
                    ]
                ];
                $data[$key]['participant'][] = $participant;
            }
        }


        return $data;
    }
}

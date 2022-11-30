<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\PatientInformationFormatter;
use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class PatientReferenceTransformer extends ModelTransformerAbstract
{
    protected string $fieldName;

    public function __construct(string $fieldName = 'patient')
    {
        $this->fieldName = $fieldName;
    }

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
        if (isset($filter['patient'])) {
            $filter = $this->transformPatientFilter($filter, 'patient');
        }
        if ($this->fieldName !== 'patient' && isset($filter[$this->fieldName])) {
            $filter = $this->transformPatientFilter($filter, $this->fieldName);
        }

        if (isset($filter['patient.email'])) {
            $value = $filter['patient.email'];
            unset($filter['patient.email']);

            $filter['gr2o_email'] = $value;
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
            $information = new PatientInformationFormatter($item);
            $data[$key][$this->fieldName] = [
                'id' => $information->getIdentifier(),
                'reference' => $information->getReference(),
                'display' => $information->getDisplayName(),
            ];
        }

        return $data;
    }

    /**
     * transform a patient field to the correct query
     *
     * @param array $filter
     * @param $patientField
     * @return array
     */
    protected function transformPatientFilter(array $filter, $patientField): array
    {
        $patientFormatter = new PatientInformationFormatter($filter);
        if (!is_array($filter[$patientField])) {
            $filter[$patientField] = [$filter[$patientField]];
        }

        $patientSearchParts = [];
        foreach($filter[$patientField] as $patient) {
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

        unset($filter[$patientField]);

        return $filter;
    }
}

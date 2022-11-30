<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Gems\Api\Fhir\PatientInformationFormatter;
use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireOwnerTransformer extends ModelTransformerAbstract
{
    protected string $fieldName;

    public function __construct(string $fieldName = 'owner')
    {
        $this->fieldName = $fieldName;
    }

    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter[$this->fieldName . '_name'])) {
            $name = $filter[$this->fieldName . '_name'];

            if (isset($filter[$this->fieldName . '_type'])) {
                switch(strtolower($filter[$this->fieldName . '_type'])) {
                    case 'patient':
                        $filter[] = [
                            'grs_first_name' => $name,
                            'grs_initials_name' => $name,
                            'grs_last_name' => $name,
                            'grs_surname_prefix' => $name,
                        ];
                        break;
                    case 'relatedperson':
                        $filter[] = [
                            'grr_first_name' => $name,
                            'grr_last_name' => $name,
                        ];
                        break;
                    case 'organization':
                        $filter['gor_name'] = $name;
                        break;
                    case 'practitioner':
                        $filter[] = [
                            'gas_name' => $name,
                            'gsf_first_name' => $name,
                            'gsf_last_name' => $name,
                            'gsf_surname_prefix' => $name,
                        ];
                        break;
                }
            } else {
                // Assume search for patient name
                $filter[] = [
                    'grs_first_name' => $name,
                    'grs_initials_name' => $name,
                    'grs_last_name' => $name,
                    'grs_surname_prefix' => $name,
                ];
            }

            unset($filter[$this->fieldName . '_name']);
        }

        if (isset($filter[$this->fieldName])) {
            $id = $filter[$this->fieldName];

            $ownerType = null;
            if (isset($filter[$this->fieldName . '_type'])) {
                $ownerType = strtolower($filter[$this->fieldName . '_type']);
            }

            if (str_starts_with($id, 'Patient/') || str_starts_with($id, Endpoints::PATIENT) || $ownerType == 'patient') {
                list($patientNr, $organizationId) = str_replace(['Patient/', Endpoints::PATIENT], '', $id);
                $filter['gr2o_patient_nr'] = $patientNr;
                $filter['gr2o_id_organization'] = $organizationId;

                $filter[$this->fieldName . '_type'] = 'patient';

            } elseif (str_starts_with($id, 'RelatedPerson/') || str_starts_with($id, Endpoints::RELATED_PERSON) || $ownerType == 'relatedperson') {
                $id = str_replace(['RelatedPerson/', Endpoints::RELATED_PERSON], '', $id);
                $filter['grr_id_relation'] = $id;

                $filter[$this->fieldName . '_type'] = 'relatedperson';

            } elseif (str_starts_with($id, 'Organization/') || str_starts_with($id, Endpoints::ORGANIZATION) || $ownerType == 'organization') {
                $id = str_replace(['Organization/', Endpoints::ORGANIZATION], '', $id);
                $filter['gto_id_organization'] = $id;

                $filter[$this->fieldName . '_type'] = 'organization';

            } elseif (str_starts_with($id, 'Practitioner/') || str_starts_with($id, Endpoints::PRACTITIONER) || $ownerType == 'practitioner') {
                $id = str_replace(['Practitioner/', Endpoints::PRACTITIONER], '', $id);
                $filter['gas_id_user'] = $id;

                $filter[$this->fieldName . '_type'] = 'practitioner';

            } elseif (str_contains($id, '@')) {
                // Assume patient if delimiter is used
                list($patientNr, $organizationId) = explode('@', str_replace(['Patient/', Endpoints::PATIENT], '', $id));
                $filter['gr2o_patient_nr'] = $patientNr;
                $filter['gr2o_id_organization'] = $organizationId;
                $filter[$this->fieldName . '_type'] = 'patient';
            }

            unset($filter[$this->fieldName]);
        }

        if (isset($filter['owner_type'])) {
            switch(strtolower($filter['owner_type'])) {
                case 'patient':
                    $filter['ggp_respondent_members'] = 1;
                    $filter[] = 'gto_id_relation IS NULL';
                    break;
                case 'relatedperson':
                    $filter['ggp_respondent_members'] = 1;
                    $filter[] = 'gto_id_relationfield IS NOT NULL';
                    $filter['gto_id_relation > 0'] = 1;
                    break;
                case 'organization':
                    $filter['ggp_staff_members'] = 1;
                    $filter[] = 'gto_by IS NULL';
                    break;
                case 'practitioner':
                    $filter['ggp_staff_members'] = 1;
                    $filter[] = 'gto_by IS NOT NULL';
                    $filter[] = 'gas_id_user IS NOT NULL';
                    break;
            }
            unset($filter['owner_type']);
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
        foreach ($data as $key => $row) {

            if ($row['ggp_respondent_members'] == 1) {
                if ($row['gto_id_relationfield'] !== null && $row['gto_id_relation'] > 0) {
                    $data[$key][$this->fieldName] = $this->getRelationReference($row);
                } else {
                    $data[$key][$this->fieldName] = $this->getPatientReference($row);

                }
            } elseif ($row['ggp_staff_members'] == 1) {
                if ($row['gto_by'] !== null && $row['gas_id_user'] !== null) {
                    $data[$key][$this->fieldName] = $this->getPractitionerReference($row);
                } else {
                    $data[$key][$this->fieldName] = $this->getOrganizationReference($row);
                }
            }

        }

        return $data;
    }

    protected function getOrganizationReference(array $row): array
    {
        return [
            'type' => 'Organization',
            'id' => $row['gto_id_organization'],
            'reference' => Endpoints::ORGANIZATION . $row['gto_id_organization'],
            'display' => $row['gor_name'],
        ];
    }

    protected function getPatientReference(array $row): array
    {
        $information = new PatientInformationFormatter($row);
        return [
            'type' => 'Patient',
            'id' => $information->getIdentifier(),
            'reference' => $information->getReference(),
            'display' => $information->getDisplayName(),
        ];
    }

    protected function getPractitionerReference(array $row): array
    {
        return [
            'type' => 'Practitioner',
            'id' => $row['gas_id_user'],
            'reference' => Endpoints::PRACTITIONER . $row['gas_id_user'],
            'display' => $row['gas_name'],
        ];
    }

    protected function getRelationReference(array $row): array
    {
        $name = '';

        if (isset($row['grr_first_name'])) {
            $name .= $row['grr_first_name'] . ' ';
        }

        if (isset($row['grr_last_name'])) {
            $name .= $row['grr_last_name'];
        }

        $relationship = 'unknown';
        if (isset($row['grr_type'])) {
            $relationship = $row['grr_type'];
        }

        return [
            'type' => 'RelatedPerson',
            'id' => $row['gto_id_relation'],
            'reference' => Endpoints::RELATED_PERSON . $row['gto_id_relation'],
            'display' => $name,
            'relationship' => $relationship,
        ];
    }
}

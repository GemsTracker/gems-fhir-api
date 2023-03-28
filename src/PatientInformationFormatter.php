<?php


namespace Gems\Api\Fhir;


class PatientInformationFormatter
{
    /**
     * @var mixed[]
     */
    protected array $data;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getIdentifier(): ?string
    {
        if (isset($this->data['gr2o_patient_nr'], $this->data['gr2o_id_organization'])) {
            return $this->data['gr2o_patient_nr'] . '@' . $this->data['gr2o_id_organization'];
        }
        return null;
    }

    public function getDisplayName(): string
    {
        $displayNameParts = [];

        if (isset($this->data['grs_last_name'])) {
            $displayNameParts[] = $this->data['grs_last_name'];
        }

        if (isset($this->data['grs_surname_prefix'])) {
            array_unshift($displayNameParts, $this->data['grs_surname_prefix']);
        }

        if (isset($this->data['grs_first_name'])) {
            array_unshift($displayNameParts, $this->data['grs_first_name']);
        } elseif (isset($this->data['grs_initials_name'])) {
            array_unshift($displayNameParts, $this->data['grs_initials_name']);
        }

        return join(' ', $displayNameParts);
    }

    public function getReference(): string
    {
        return $this->getPatientEndpoint() . $this->getIdentifier();
    }

    public function getPatientEndpoint(): string
    {
        return Endpoints::PATIENT;
    }
}

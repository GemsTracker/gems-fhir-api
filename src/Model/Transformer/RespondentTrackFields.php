<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Agenda\Repository\AgendaStaffRepository;
use Gems\Agenda\Repository\LocationRepository;
use Gems\Model\RespondentTrackFieldDataModel;

trait RespondentTrackFields
{
    protected RespondentTrackFieldDataModel $respondentTrackFieldsDataModel;

    protected AgendaStaffRepository $agendaStaffRepository;

    protected LocationRepository $locationRepository;

    /**
     * @param mixed[] $trackFieldInfo
     * @return string|null
     */
    protected function getDisplayValue(array $trackFieldInfo): ?string
    {
        if ($trackFieldInfo['gr2t2f_value'] === null) {
            return null;
        }
        return match ($trackFieldInfo['gtf_field_type']) {
            'caretaker' => $this->getCaretakerName($trackFieldInfo['gr2t2f_value']),
            'location' => $this->getLocationName($trackFieldInfo['gr2t2f_value']),
            default => null,
        };
    }

    protected function getCaretakerName(int $caretakerId): ?string
    {
        return $this->agendaStaffRepository->getStaffNameFromId($caretakerId);
    }

    protected function getLocationName(int $locationId): ?string
    {
        return $this->locationRepository->getLocationName($locationId);
    }

    /**
     * @param int $respondentTrackId
     * @return mixed[]
     */
    protected function getTrackfields(int $respondentTrackId): array
    {
        $model = $this->getTrackfieldModel();

        return $model->load(
            [
                'gr2t_id_respondent_track' => $respondentTrackId,
            ]
        );
    }

    protected function getTrackfieldModel(): RespondentTrackFieldDataModel
    {
        return $this->respondentTrackFieldsDataModel;
    }
}

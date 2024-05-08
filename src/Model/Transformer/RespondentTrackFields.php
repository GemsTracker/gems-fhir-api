<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Model\RespondentTrackFieldDataModel;
use MUtil\Model\TableModel;

trait RespondentTrackFields
{
    protected RespondentTrackFieldDataModel $respondentTrackFieldsDataModel;
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
        $model = new TableModel('gems__agenda_staff');
        $result = $model->loadFirst(['gas_id_staff' => $caretakerId]);
        if ($result) {
            return $result['gas_name'];
        }
        return null;
    }

    protected function getLocationName(int $locationId): ?string
    {
        $model = new TableModel('gems__locations');
        $result = $model->loadFirst(['glo_id_location' => $locationId]);
        if ($result) {
            return $result['glo_name'];
        }
        return null;
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

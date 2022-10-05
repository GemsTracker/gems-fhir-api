<?php

namespace Gems\Api\Fhir\Model\Transformer;


use Gems\Api\Fhir\Endpoints;
use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class CarePlanInfoTransformer extends ModelTransformerAbstract
{
    use RespondentTrackFields;

    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            $respondentTrackId = $row['gr2t_id_respondent_track'];
            $info = [];

            $trackfieldData = $this->getTrackfields($respondentTrackId);

            foreach($trackfieldData as $trackFieldRow) {
                if ($trackFieldRow['type'] == 'appointment') {
                    $infoRow = [
                        'name' => $trackFieldRow['gtf_field_name'],
                        'type' => 'appointmentField',
                        'value' => null,
                    ];
                    if ($trackFieldRow['gr2t2f_value'] !== null) {
                        $infoRow['value'] = [
                            'type' => 'Appointment',
                            'id' => (int)$trackFieldRow['gr2t2f_value'],
                            'reference' => Endpoints::APPOINTMENT . $trackFieldRow['gr2t2f_value'],
                        ];
                    }
                } else {
                    $infoRow = [
                        'name' => $trackFieldRow['gtf_field_name'],
                        'type' => 'trackField',
                        'value' => $trackFieldRow['gr2t2f_value'],
                    ];
                    if ($displayValue = $this->getDisplayValue($trackFieldRow)) {
                        $infoRow['display'] = $displayValue;
                    }
                }
                if (isset($trackFieldRow['gtf_field_code'])) {
                    $infoRow['code'] = $trackFieldRow['gtf_field_code'];
                }
                $info[] = $infoRow;
            }

            $data[$key]['supportingInfo'] = $info;
        }

        return $data;
    }
}

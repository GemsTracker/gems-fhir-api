<?php

namespace Gems\Api\Fhir\Model\Transformer;


use Gems\Api\Fhir\Endpoints;
use Gems\Model\RespondentTrackFieldDataModel;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class CarePlanInfoTransformer extends ModelTransformerAbstract
{
    use RespondentTrackFields;

    public function __construct(
        RespondentTrackFieldDataModel $respondentTrackFieldDataModel,
    )
    {
        $this->respondentTrackFieldsDataModel = $respondentTrackFieldDataModel;
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

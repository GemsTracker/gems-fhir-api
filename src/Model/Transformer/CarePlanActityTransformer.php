<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;

class CarePlanActityTransformer extends \MUtil_Model_ModelTransformerAbstract
{
    /**
     * @var \Gems_Tracker_TrackerInterface
     */
    protected \Gems_Tracker_TrackerInterface $tracker;

    public function __construct(\Gems_Tracker_TrackerInterface $tracker)
    {
        $this->tracker = $tracker;
    }

    public function transformLoad(\MUtil_Model_ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach ($data as $key => $row) {
            $respondentTrackId = $row['gr2t_id_respondent_track'];
            $tokens = $this->getTrackTokens($respondentTrackId);
            $tokenRows = [];
            foreach($tokens as $token) {
                $tokenRow = [
                    'reference' => [
                        'type' => 'Questionnaire-task',
                        'id' => $token['gto_id_token'],
                        'reference' => Endpoints::QUESTIONNAIRE_TASK . $token['gto_id_token'],
                    ],
                ];
                $tokenRows[] = $tokenRow;
            }
            if (count($tokenRows)) {
                $data[$key]['activity'] = $tokenRows;
            }
        }

        return $data;
    }

    protected function getTrackTokens($respondentTrackId): array
    {
        $tokenSelect = $this->tracker->getTokenSelect(['gto_id_token']);
        $tokenSelect->andReceptionCodes([]);
        $tokenSelect->forRespondentTrack($respondentTrackId);
        $tokenSelect->onlySucces();

        return $tokenSelect->fetchAll();
    }
}

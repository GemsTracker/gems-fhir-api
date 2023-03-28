<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Gems\Tracker\TrackerInterface;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class CarePlanActityTransformer extends ModelTransformerAbstract
{
    /**
     * @var TrackerInterface
     */
    protected TrackerInterface $tracker;

    public function __construct(TrackerInterface $tracker)
    {
        $this->tracker = $tracker;
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

    /**
     * @param int $respondentTrackId
     * @return mixed[]
     * @throws \Zend_Db_Select_Exception
     */
    protected function getTrackTokens(int $respondentTrackId): array
    {
        $tokenSelect = $this->tracker->getTokenSelect(['gto_id_token']);
        $tokenSelect->andReceptionCodes([]);
        $tokenSelect->forRespondentTrack($respondentTrackId);
        $tokenSelect->onlySucces();

        return $tokenSelect->fetchAll();
    }
}

<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireTaskInfoTransformer extends ModelTransformerAbstract
{
    /**
     * @var ?string
     */
    protected ?string $currentUri;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected \Zend_Db_Adapter_Abstract $db;
    /**
     * @var array|null
     */
    protected ?array $respondentTrackReceptionCodes;

    public function __construct(\Zend_Db_Adapter_Abstract $db, ?string $currentUri = null)
    {
        $this->db = $db;
        $this->currentUri = $currentUri;
    }

    protected function getRespondentTrackReceptionCodes(): array
    {
        if (!$this->respondentTrackReceptionCodes) {
            $select = $this->db->select();
            $select->from('gems__reception_codes', ['grc_id_reception_code' => 'grc_success'])
                ->where('grc_for_tracks = 1')
                ->where('grc_active = 1');

            $this->respondentTrackReceptionCodes = $this->db->fetchPairs($select);
        }

        return $this->respondentTrackReceptionCodes;
    }

    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['roundDescription'])) {
            $filter['gto_round_description'] = $filter['roundDescription'];
            unset($filter['roundDescription']);
        }

        if (isset($filter['track'])) {
            $filter['gto_id_track'] = $filter['track'];
            unset($filter['track']);
        }

        if (isset($filter['track_name'])) {
            $filter['gtr_track_name'] = $filter['trackName'];
            unset($filter['trackName']);
        }

        if (isset($filter['track_code'])) {
            $filter['gtr_code'] = $filter['track_code'];
            unset($filter['track_code']);
        }

        if (isset($filter['carePlan'])) {
            $filter['gto_id_respondent_track'] = $filter['carePlan'];
            unset($filter['carePlan']);
        }

        if (isset($filter['respondentTrackId'])) {
            $filter['gto_id_respondent_track'] = $filter['respondentTrackId'];
            unset($filter['respondentTrackId']);
        }

        if (isset($filter['carePlanSuccess'])) {
            $receptionCodes = $this->getRespondentTrackReceptionCodes();
            $expectedStatus = (int)$filter['carePlanSuccess'];

            $filteredReceptionCodes = array_filter($receptionCodes, function($value, $key) use ($expectedStatus) {
                return $value == $expectedStatus;
            });

            $filter['gr2t_reception_code'] = array_keys($filteredReceptionCodes);
            unset($filter['carePlanSuccess']);
        }

        return $filter;
    }

    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$row) {
            $info = [];
            if (isset($row['gto_round_description'])) {
                $info[] = [
                    'type' => 'roundDescription',
                    'value' => $row['gto_round_description'],
                ];
            }

            if (isset($row['gto_round_order'])) {
                $info[] = [
                    'type' => 'roundOrder',
                    'value' => $row['gto_round_order'],
                ];
            }

            $loginUrl = $this->getLoginUrl($row);
            $info[] = [
                'type' => 'url',
                'value' => $loginUrl . '/ask/to-survey/id/' . $row['gto_id_token'],
            ];

            if (isset($row['gto_id_track'])) {
                $info[] = [
                    'type' => 'track',
                    'value' => $row['gtr_track_name'],
                ];
            }

            if (isset($row['gto_id_respondent_track'])) {
                $info[] = [
                    'type' => 'CarePlan',
                    'id' => $row['gto_id_respondent_track'],
                    'reference' => Endpoints::CARE_PLAN . $row['gto_id_respondent_track'],
                    'display' => $row['gtr_track_name'],
                ];
            }

            $data[$key]['info'] = $info;
        }

        return $data;
    }

    protected function getLoginUrl(array $row): string
    {
        if (array_key_exists('gor_url_base', $row) && $row['gor_url_base'] !== null && $baseUrls = explode(' ', $row['gor_url_base'])) {
            $baseUrl = reset($baseUrls);
            if (!empty($baseUrl)) {
                return $baseUrl;
            }
        }

        return $this->currentUri;
    }
}

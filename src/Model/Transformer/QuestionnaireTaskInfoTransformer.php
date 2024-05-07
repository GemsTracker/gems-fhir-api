<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Gems\Db\ResultFetcher;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class QuestionnaireTaskInfoTransformer extends ModelTransformerAbstract
{
    /**
     * @var string
     */
    protected string $currentUri;

    /**
     * @var int[]|null
     */
    protected ?array $respondentTrackReceptionCodes = null;

    public function __construct(
        protected readonly ResultFetcher $resultFetcher,
        string|null $currentUri = null)
    {
        $this->currentUri = $currentUri ?? '/';
    }

    /**
     * @return int[]
     */
    protected function getRespondentTrackReceptionCodes(): array
    {
        if (!$this->respondentTrackReceptionCodes) {
            $select = $this->resultFetcher->getSelect('gems__reception_codes');
            $select->columns(['grc_id_reception_code', 'grc_success'])
                ->where([
                    'grc_for_tracks' => 1,
                    'grc_active' => 1,
                ]);

            $this->respondentTrackReceptionCodes = $this->resultFetcher->fetchPairs($select);
        }

        return $this->respondentTrackReceptionCodes;
    }

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
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

            $filteredReceptionCodes = array_filter($receptionCodes, function($value) use ($expectedStatus) {
                return $value == $expectedStatus;
            });

            $filter['gr2t_reception_code'] = array_keys($filteredReceptionCodes);
            unset($filter['carePlanSuccess']);
        }

        return $filter;
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

            $loginUrl = rtrim($this->getLoginUrl($row), '/');
            $info[] = [
                'type' => 'url',
                'value' => $loginUrl . '/ask/to-survey/' . $row['gto_id_token'],
            ];

            if (isset($row['gto_id_track'])) {
                $info[] = [
                    'type' => 'track',
                    'value' => $row['gtr_track_name'],
                ];
                if (isset($row['gtr_code'])) {
                    $info[] = [
                        'type' => 'trackCode',
                        'value' => $row['gtr_code'],
                    ];
                }
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

    /**
     * @param mixed[] $row
     * @return string
     */
    protected function getLoginUrl(array $row): string
    {
        if ($row['gor_url_base'] !== null) {
            $baseUrls = explode(' ', $row['gor_url_base']);
            if (array_key_exists('gor_url_base', $row)) {
                $baseUrl = reset($baseUrls);
                if (!empty($baseUrl)) {
                    return $baseUrl;
                }
            }
        }

        return $this->currentUri;
    }
}

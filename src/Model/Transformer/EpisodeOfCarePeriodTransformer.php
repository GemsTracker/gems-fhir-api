<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class 
EpisodeOfCarePeriodTransformer extends ModelTransformerAbstract
{
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['start'])) {
            $filter['gec_startdate'] = $filter['start'];
            unset($filter['start']);
        }
        if (isset($filter['end'])) {
            $filter['gec_enddate'] = $filter['end'];
            unset($filter['end']);
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
        foreach($data as $key=>$item) {
            $period = [];
            if (isset($item['gec_startdate'])) {
                $period['start'] = $item['gec_startdate'];
            }
            if (isset($item['gec_enddate'])) {
                $period['end'] = $item['gec_enddate'];
            }

            if (count($period)) {
                $data[$key]['period'] = $period;
            }
        }

        return $data;
    }
}

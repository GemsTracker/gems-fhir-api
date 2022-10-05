<?php


namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class EpisodeOfCarePeriodTransformer extends ModelTransformerAbstract
{
    public function transformFilter(ModelAbstract $model, array $filter): array
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

    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
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

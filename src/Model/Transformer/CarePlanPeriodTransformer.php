<?php

namespace Gems\Api\Fhir\Model\Transformer;

use DateTimeInterface;
use DateTimeImmutable;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class CarePlanPeriodTransformer extends ModelTransformerAbstract
{
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
            $validFrom = null;
            if ($row['gr2t_start_date']) {
                $validFrom = $row['gr2t_start_date'];
                if (!$validFrom instanceof DateTimeInterface) {
                    $validFrom = new DateTimeImmutable($validFrom);
                }
            }

            $data[$key]['period']['start'] = null;
            if ($validFrom instanceof DateTimeInterface) {
                $data[$key]['period']['start'] = $validFrom->format(DateTimeInterface::ATOM);
            }

            $validUntil = null;
            if ($row['gr2t_end_date']) {
                $validUntil = $row['gr2t_end_date'];
                if (!$validUntil instanceof DateTimeInterface) {
                    $validUntil = new DateTimeImmutable($validUntil);
                }
            }

            $data[$key]['period']['end'] = null;
            if ($validUntil instanceof DateTimeInterface) {
                $data[$key]['period']['end'] = $validUntil->format(DateTimeInterface::ATOM);
            }
        }
        return $data;
    }
}

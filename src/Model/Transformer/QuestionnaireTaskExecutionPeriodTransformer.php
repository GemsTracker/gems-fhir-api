<?php

namespace Gems\Api\Fhir\Model\Transformer;

use DateTimeInterface;
use DateTimeImmutable;
use Zalt\Model\MetaModel;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class QuestionnaireTaskExecutionPeriodTransformer extends ModelTransformerAbstract
{
    /**
     * @param MetaModelInterface $model
     * @param mixed[] $data
     * @param $new
     * @param $isPostData
     * @return mixed[]
     * @throws \Exception
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach ($data as $key => $row) {
            $validFrom = null;
            if ($row['gto_valid_from']) {
                $validFrom = $row['gto_valid_from'];
                
                if (!$validFrom instanceof DateTimeInterface) {
                    $validFrom = new DateTimeImmutable($validFrom);
                }
            }

            $data[$key]['executionPeriod']['start'] = null;
            if ($validFrom instanceof DateTimeInterface) {
                $data[$key]['executionPeriod']['start'] = $validFrom->format(DateTimeInterface::ATOM);
            }

            $validUntil = null;
            if ($row['gto_valid_until']) {
                $validUntil = $row['gto_valid_until'];

                if (!$validUntil instanceof DateTimeInterface) {
                    $validUntil = new DateTimeImmutable($validUntil);
                }
            }

            $data[$key]['executionPeriod']['end'] = null;
            if ($validUntil instanceof DateTimeInterface) {
                $data[$key]['executionPeriod']['end'] = $validUntil->format(DateTimeInterface::ATOM);
            }
        }
        return $data;
    }

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $row
     * @return mixed[]
     * @throws \Exception
     */
    public function transformRowBeforeSave(MetaModelInterface $model, array $row): array
    {
        if (isset($row['executionPeriod'])) {
            if (array_key_exists('start', $row['executionPeriod'])) {
                if ($row['executionPeriod']['start'] === null) {
                    $row['gto_valid_from_manual'] = 0;
                    $row['gto_valid_from'] = null;
                } else {
                    $row['gto_valid_from_manual'] = 1;
                    $row['gto_valid_from'] = new DateTimeImmutable($row['executionPeriod']['start']);
                }
            }
            if (array_key_exists('end', $row['executionPeriod'])) {
                if ($row['executionPeriod']['end'] === null) {
                    $row['gto_valid_until_manual'] = 0;
                    $row['gto_valid_until'] = null;
                } else {
                    $row['gto_valid_until_manual'] = 1;
                    $row['gto_valid_until'] = new DateTimeImmutable($row['executionPeriod']['end']);
                }
            }
        }

        return $row;
    }
}

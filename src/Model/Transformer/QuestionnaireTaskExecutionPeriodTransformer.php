<?php

namespace Gems\Api\Fhir\Model\Transformer;

use DateTimeInterface;
use DateTimeImmutable;
use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireTaskExecutionPeriodTransformer extends ModelTransformerAbstract
{
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

    public function transformRowBeforeSave(MetaModelInterface $model, array $row): array
    {
        if (isset($row['executionPeriod'])) {
            if (array_key_exists('start', $row['executionPeriod'])) {
                if ($row['executionPeriod']['start'] === null) {
                    $row['gto_valid_from_manual'] = 0;
                    $row['gto_valid_from'] = null;
                } else {
                    $start = new DateTimeImmutable($row['executionPeriod']['start']);
                    $row['gto_valid_from_manual'] = 1;
                    $row['gto_valid_from'] = $start->format('Y-m-d H:i:s');
                }
                $model->remove('gto_valid_from', $model::SAVE_TRANSFORMER);
            }
            if (array_key_exists('end', $row['executionPeriod'])) {
                if ($row['executionPeriod']['end'] === null) {
                    $row['gto_valid_until_manual'] = 0;
                    $row['gto_valid_until'] = null;
                } else {
                    $end = new DateTimeImmutable($row['executionPeriod']['end']);
                    $row['gto_valid_until_manual'] = 1;
                    $row['gto_valid_until'] = $end->format('Y-m-d H:i:s');
                }
                $model->remove('gto_valid_until', $model::SAVE_TRANSFORMER);
            }
        }

        return $row;
    }
}

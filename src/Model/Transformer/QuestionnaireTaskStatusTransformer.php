<?php

namespace Gems\Api\Fhir\Model\Transformer;

use DateTimeInterface;
use DateTimeImmutable;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class QuestionnaireTaskStatusTransformer extends ModelTransformerAbstract
{
    protected function getFilterPartFromStatus(string $status): ?string
    {
        return match ($status) {
            'completed' => '(gto_completion_time IS NOT NULL AND grc_success = 1)',
            'rejected' => '(gto_completion_time IS NULL AND gto_valid_until IS NOT NULL AND gto_valid_until < NOW() AND grc_success = 1)',
            'draft' => '((gto_valid_from IS NULL OR gto_valid_from > NOW()) AND gto_start_time IS NULL AND grc_success = 1)',
            'requested' => '(gto_completion_time IS NULL AND gto_start_time IS NULL AND gto_valid_from IS NOT NULL AND gto_valid_from < NOW() AND (gto_valid_until > NOW() OR gto_valid_until IS NULL)  AND grc_success = 1)',
            'in-progress' => '(gto_completion_time IS NULL AND gto_start_time IS NOT NULL AND gto_valid_from IS NOT NULL AND gto_valid_from < NOW() AND (gto_valid_until > NOW() OR gto_valid_until IS NULL)  AND grc_success = 1)',
            default => null,
        };
    }

    /**
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[]
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $filterParts = [];
                foreach($filter['status'] as $status) {
                    $filterParts[] = $this->getFilterPartFromStatus($status);
                }
                if (count($filterParts)) {
                    $filter[] = $filterParts;
                }
            } else {
                $filterPart = $this->getFilterPartFromStatus($filter['status']);
                if ($filterPart) {
                    $filter[] = $filterPart;
                }
            }
            unset($filter['status']);
        }

        return $filter;
    }

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
        foreach($data as $key=>$row) {
            $now = new DateTimeImmutable();

            $validFrom = null;
            if ($row['gto_valid_from']) {
                $validFrom = $row['gto_valid_from'];

                if (!$validFrom instanceof DateTimeInterface) {
                    $validFrom = new DateTimeImmutable($validFrom);
                }
            }

            $validUntil = null;
            if ($row['gto_valid_until']) {
                $validUntil = $row['gto_valid_until'];
                
                if (!$validUntil instanceof DateTimeInterface) {
                    $validUntil = new DateTimeImmutable($validUntil);
                }
            }

            if (($validFrom === null || $now < $validFrom) && $row['grc_success'] == 1 && $row['gto_start_time'] === null && ($validUntil === null || $now < $validUntil)) {
                $data[$key]['status'] = 'draft';
                continue;
            }

            if ($row['gto_completion_time'] !== null && $row['grc_success'] == 1) {
                $data[$key]['status'] = 'completed';
                continue;
            }

            if ($validFrom && $now >= $validFrom && ($validUntil === null || $now < $validUntil) && $row['grc_success'] == 1 && $row['gto_completion_time'] === null) {
                if ($row['gto_start_time'] !== null) {
                    $data[$key]['status'] = 'in-progress';
                    continue;
                }
                $data[$key]['status'] = 'requested';
                continue;
            }

            if ($validUntil && $row['gto_completion_time'] === null && $row['grc_success'] == 1 && $now > $validUntil) {
                $data[$key]['status'] = 'rejected';
                continue;
            }

            $data[$key]['status'] = 'unknown';
        }

        return $data;
    }
}

<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Db\ResultFetcher;
use Laminas\Db\Sql\Expression;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class PatientHumanNameTransformer extends ModelTransformerAbstract
{
   public function __construct(
       private readonly ResultFetcher $resultFetcher,
   )
   {
   }

    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[] The (optionally changed) filter
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (isset($filter['name'])) {
            $value = $filter['name'];
            $filter[] = [
                'grs_first_name' => $value,
                'grs_initials_name' => $value,
                'grs_last_name' => $value,
                'grs_surname_prefix' => $value,
            ];

            unset($filter['name']);
        }

        if (isset($filter['family'])) {
            $value = '%'.$filter['family'].'%';
            $platform = $this->resultFetcher->getPlatform();
            $value = $platform->quoteValue($value);
            $filter[] = new Expression("CONCAT_WS(' ', grs_surname_prefix, grs_last_name) LIKE " . $value);

            unset($filter['family']);
        }

        if (isset($filter['given'])) {
            $value = '%'.$filter['given'].'%';
            $platform = $this->resultFetcher->getPlatform();
            $value = $platform->quoteValue($value);
            $filter[] = "(grs_first_name LIKE ".$value.")
             OR (grs_initials_name LIKE ".$value.")";

            unset($filter['given']);
        }

        return $filter;
    }


    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param MetaModelInterface $model The parent model
     * @param mixed[] $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return mixed[] Nested array containing (optionally) transformed data
     */
    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$item) {
            $familyName = $item['grs_last_name'];
            if (isset($item['grs_surname_prefix'])) {
                $familyName = $item['grs_surname_prefix'] . ' ' . $familyName;
            }

            $givenNames = [];

            if (isset($item['grs_first_name'])) {
                $givenNames[] = [
                    'value' => $item['grs_first_name'],
                    'extension' => [
                        [
                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                            'valueCode' => 'LS',
                        ]
                    ]
                ];
            }

            if (isset($item['grs_initials_name'])) {
                $givenNames[] = [
                    'value' => $item['grs_initials_name'],
                    'extension' => [
                        [
                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                            'valueCode' => 'IN',
                        ]
                    ]
                ];
            }

            $nameParts = [
                'family' => $familyName,
            ];
            if ($givenNames) {
                $nameParts['given'] = $givenNames;
            }

            $data[$key]['name'][] = $nameParts;
        }

        return $data;
    }
}

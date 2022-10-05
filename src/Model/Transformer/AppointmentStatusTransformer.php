<?php


namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class AppointmentStatusTransformer extends ModelTransformerAbstract
{
    public static array $statusTranslation = [
        'AB' => 'cancelled',
        'AC' => 'booked',
        'CA' => 'cancelled',
        'CO' => 'fulfilled',
    ];

    public static array $reverseStatusTranslation = [
        'booked' => 'AC',
        'cancelled' => ['AB', 'CA'],
        'fulfilled' => 'CO',
    ];

    public string $statusField = 'gap_status';

    /**
     * This transform function checks the filter for
     * a) retreiving filters to be applied to the transforming data,
     * b) adding filters that are needed
     *
     * @param ModelAbstract $model
     * @param array $filter
     * @return array The (optionally changed) filter
     */
    public function transformFilter(ModelAbstract $model, array $filter): array
    {
        $reversedStatusTranslations = self::$reverseStatusTranslation;

        if (isset($filter[$this->statusField])) {
            if (is_array($filter[$this->statusField])) {
                $translatedStatus = [];
                foreach ($filter[$this->statusField] as $key => $status) {
                    if (isset($reversedStatusTranslations[$status])) {
                        if (is_array($reversedStatusTranslations[$status])) {
                            $translatedStatus += $reversedStatusTranslations[$status];
                        } else {
                            $translatedStatus[] = $reversedStatusTranslations[$status];
                        }
                    }
                }
                if (count($translatedStatus)) {
                    $filter[$this->statusField] = $translatedStatus;
                } else {
                    unset($filter[$this->statusField]);
                }
            } elseif (isset($reversedStatusTranslations[$filter[$this->statusField]])) {
                $filter[$this->statusField] = $reversedStatusTranslations[$filter[$this->statusField]];
            }
        }

        return $filter;
    }

    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$item) {
            if (isset($item[$this->statusField]) && isset(self::$statusTranslation[$item[$this->statusField]])) {
                $data[$key][$this->statusField] = self::$statusTranslation[$item[$this->statusField]];
            }
        }

        return $data;
    }
}

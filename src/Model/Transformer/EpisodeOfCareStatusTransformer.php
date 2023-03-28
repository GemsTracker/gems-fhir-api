<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class EpisodeOfCareStatusTransformer extends ModelTransformerAbstract
{
    /**
     * @var string[]
     */
    public static array $statusTranslation = [
        'A' => 'active',
        'F' => 'finished',
        'O' => 'onhold',
        'P' => 'planned',
        'W' => 'waitlist',
        'C' => 'cancelled',
        'E' => 'entered-in-error',
    ];

    public string $statusField = 'gec_status';

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
        $reversedStatusTranslations = array_flip(self::$statusTranslation);

        if (isset($filter[$this->statusField], $reversedStatusTranslations[$filter[$this->statusField]])) {
            $filter[$this->statusField] = $reversedStatusTranslations[$filter[$this->statusField]];
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
            if (isset($item[$this->statusField]) && isset(self::$statusTranslation[$item[$this->statusField]])) {
                $data[$key][$this->statusField] = self::$statusTranslation[$item[$this->statusField]];
            }
        }

        return $data;
    }
}

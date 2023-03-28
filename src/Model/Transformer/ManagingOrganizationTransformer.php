<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use MUtil\Model\DatabaseModelAbstract;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class ManagingOrganizationTransformer extends ModelTransformerAbstract
{
    protected string $fieldName = 'managingOrganization';

    /**
     * Field in model pointing to organization ID
     *
     * @var string
     */
    protected string $organizationIdField;

    /**
     * Is the gems__organization table joined in the model?
     *
     * @var bool
     */
    protected bool $organizationJoined;

    public function __construct(string $organizationIdField, bool $organizationJoined=true, string $fieldName = 'managingOrganization')
    {
        $this->organizationIdField = $organizationIdField;
        $this->organizationJoined = $organizationJoined;
        $this->fieldName = $fieldName;
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
        if (isset($filter[$this->fieldName])) {
            $value = (int)str_replace(['Organization/', Endpoints::ORGANIZATION], '', $filter[$this->fieldName]);
            $filter[$this->organizationIdField] = $value;

            unset($filter[$this->fieldName]);
        }
        if (isset($filter['organization'])) {
            $value = (int)str_replace(['Organization/', Endpoints::ORGANIZATION], '', $filter['organization']);
            $filter[$this->organizationIdField] = $value;

            unset($filter['organization']);
        }

        if ($this->organizationJoined) {
            if (isset($filter['organization.name'])) {
                $value = '%'.$filter['organization.name'].'%';
                if ($model instanceof DatabaseModelAbstract) {
                    $adapter = $model->getAdapter();
                    $value = $adapter->quote($value);
                    $filter[] = "gor_name LIKE " . $value;
                }

                unset($filter['organization.name']);
            }

            if (isset($filter['organization.code'])) {
                $value = $filter['organization.code'];
                $filter['gor_code'] = $value;

                unset($filter['organization.code']);
            }
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
        foreach ($data as $key => $item) {
            $data[$key][$this->fieldName]['id'] = $item[$this->organizationIdField];
            $data[$key][$this->fieldName]['reference'] = Endpoints::ORGANIZATION . $item[$this->organizationIdField];
            if ($this->organizationJoined) {
                $data[$key][$this->fieldName]['display'] = $item['gor_name'];
            }
        }

        return $data;
    }
}

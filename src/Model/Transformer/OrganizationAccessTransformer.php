<?php


namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Repository\OrganizationRepository;
use Zalt\Model\MetaModelInterface;
use Zalt\Model\Transform\ModelTransformerAbstract;

class OrganizationAccessTransformer extends ModelTransformerAbstract
{
    const FIELD_NAME = 'full';

    public function __construct(
        protected readonly OrganizationRepository $organizationRepository,
    ) {
    }

    /**
     * This transform function checks the filter for the presence of the
     * field name that indicates we want to retrieve data for all organizations
     * that we have access to.
     * If that field is present and true, we replace the existing organization ID
     * in the filter by an array of accessible organization IDs.
     *
     * @param MetaModelInterface $model
     * @param mixed[] $filter
     * @return mixed[] The (optionally changed) filter
     */
    public function transformFilter(MetaModelInterface $model, array $filter): array
    {
        if (!isset($filter[self::FIELD_NAME])) {
            return $filter;
        }
        if ($filter[self::FIELD_NAME] !== 'true') {
            unset($filter[self::FIELD_NAME]);
            return $filter;
        }
        unset($filter[self::FIELD_NAME]);
        // If there is no organization filter at all, we can't filter on multiple organizations.
        if (!isset($filter['gr2o_id_organization'])) {
            return $filter;
        }
        // If we're already filtering on multiple organizations, don't change that.
        if (is_array($filter['gr2o_id_organization'])) {
            return $filter;
        }
        $filter['gr2o_id_organization'] = array_keys($this->organizationRepository->getAllowedOrganizationsFor($filter['gr2o_id_organization']));

        return $filter;
    }
}

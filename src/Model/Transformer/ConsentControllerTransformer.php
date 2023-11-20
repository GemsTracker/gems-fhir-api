<?php

namespace Gems\Api\Fhir\Model\Transformer;

use Gems\Api\Fhir\Endpoints;
use Zalt\Model\MetaModelInterface;

class ConsentControllerTransformer extends ManagingOrganizationTransformer
{
    public function __construct(
    ) {
        parent::__construct('gr2o_id_organization', true, 'controller');
    }

    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        foreach ($data as $key => $item) {
            $controller = [
                'id' => $item[$this->organizationIdField],
                'reference' => Endpoints::ORGANIZATION . $item[$this->organizationIdField],
                'display' => $item['gor_name'],
            ];

            $data[$key]['controller'][] = $controller;
        }

        return $data;
    }
}
<?php


namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\BooleanTransformer;
use Gems\Api\Fhir\Model\Transformer\OrganizationContactTransformer;
use Gems\Api\Fhir\Model\Transformer\OrganizationTelecomTransformer;

class OrganizationModel extends \Gems_Model_OrganizationModel
{
    public function __construct()
    {
        parent::__construct([]);

        $this->addColumn(new \Zend_Db_Expr('\'Organization\''), 'resourceType');

        $this->set('resourceType', 'label', 'resourceType');

        $this->set('gor_id_organization', 'label','id', 'apiName', 'id');
        $this->set('gor_active', 'label','active', 'apiName', 'active');
        $this->set('gor_name', 'label','name', 'apiName', 'name');
        $this->set('telecom', 'label','telecom');
        $this->set('contact', 'label','contact');
        $this->set('gor_code', 'label','code', 'apiName', 'code');

        $this->addTransformer(new OrganizationTelecomTransformer());
        $this->addTransformer(new OrganizationContactTransformer());
        $this->addTransformer(new BooleanTransformer(['gor_active']));
    }
}

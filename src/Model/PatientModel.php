<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\BooleanTransformer;
use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientHumanNameTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientIdTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientTelecomTransformer;

class PatientModel extends \Gems_Model_RespondentModel
{
    public function __construct()
    {
        parent::__construct();

        $this->addTable('gems__organizations', ['gr2o_id_organization' => 'gor_id_organization'], 'gor', false);

        $this->addColumn(new \Zend_Db_Expr('CONCAT(gr2o_patient_nr, "@", gr2o_id_organization)'), 'id');
        //$this->addColumn('grc_success', 'active');
        $this->addColumn(new \Zend_Db_Expr("CASE grs_gender WHEN 'M' THEN 'male' WHEN 'F' THEN 'female' ELSE 'unknown' END"), 'gender');

        $this->addColumn(new \Zend_Db_Expr('\'Patient\''), 'resourceType');

        $this->set('resourceType', 'label', 'resourceType');

        $this->set('id', 'label', 'id');
        $this->set('grc_success', 'label', 'active', 'apiName', 'active');
        $this->set('gender', 'label', 'gender');
        $this->set('grs_birthday', 'label', 'birthDate', 'apiName', 'birthDate');

        $this->set('name', 'label', 'name');
        $this->set('gr2o_created', 'label', 'created', 'apiName', 'created');
        $this->set('gr2o_changed', 'label', 'changed', 'apiName', 'changed');

        // search options
        $this->set('family', 'label', 'name');
        $this->set('given', 'label', 'name');



        $this->set('telecom', 'label', 'telecom');
        // search options
        $this->set('email', 'label', 'email');
        $this->set('phone', 'label', 'phone');

        $this->set('managingOrganization', 'label', 'managingOrganization');
        // search options
        $this->set('organization', 'label', 'organization');
        $this->set('organization_name', 'label', 'organization_name');
        $this->set('organization_code', 'label', 'organization_code');

        $this->addTransformer(new PatientIdTransformer());
        $this->addTransformer(new PatientHumanNameTransformer());
        $this->addTransformer(new PatientTelecomTransformer());
        $this->addTransformer(new ManagingOrganizationTransformer('gr2o_id_organization', true));
        $this->addTransformer(new BooleanTransformer(['grc_success']));
    }
}

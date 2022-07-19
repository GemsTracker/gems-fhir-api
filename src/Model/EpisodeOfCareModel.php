<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\EpisodeOfCarePeriodTransformer;
use Gems\Api\Fhir\Model\Transformer\EpisodeOfCareStatusTransformer;
use Gems\Api\Fhir\Model\Transformer\IntTransformer;
use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use MUtil\Model\Type\JsonData;

class EpisodeOfCareModel extends \Gems_Model_JoinModel
{
    public function __construct()
    {
        parent::__construct('episodesofcare', 'gems__episodes_of_care', 'gec');

        $this->addColumn(new \Zend_Db_Expr('\'EpisodeOfCare\''), 'resourceType');

        $this->set('resourceType', 'label', 'resourceType');

        $this->addTable('gems__respondent2org', ['gec_id_user' => 'gr2o_id_user', 'gec_id_organization', 'gr2o_id_organization'], 'gr2o', false);
        $this->addTable('gems__organizations', ['gec_id_organization' => 'gor_id_organization'], 'gor', false);


        $this->set('gec_episode_of_care_id', 'label', 'id', 'apiName', 'id');
        $this->set('gec_status', 'label', 'status', 'apiName', 'status');
        $this->set('patient', 'label', 'patient', 'apiName', 'patient');
        $this->set('period', 'label', 'period', 'apiName', 'period');
        $this->set('managingOrganization', 'label', 'managingOrganization', 'apiName', 'managingOrganization');

        $jsonType = new JsonData(10);
        $jsonType->apply($this, 'gec_diagnosis_data', false);
        $jsonType->apply($this, 'gec_extra_data',     false);

        $this->addTransformer(new EpisodeOfCareStatusTransformer());
        $this->addTransformer(new EpisodeOfCarePeriodTransformer());
        $this->addTransformer(new PatientReferenceTransformer('patient'));
        $this->addTransformer(new ManagingOrganizationTransformer('gec_id_organization', true));
        $this->addTransformer(new IntTransformer(['gec_episode_of_care_id']));
    }
}

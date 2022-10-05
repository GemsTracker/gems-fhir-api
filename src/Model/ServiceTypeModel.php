<?php


namespace Gems\Api\Fhir\Model;


use Gems\Model\JoinModel;

class ServiceTypeModel extends JoinModel
{
    public function __construct()
    {
        parent::__construct('serviceType', 'gems__agenda_activities', 'gaa', false);

        $this->set('gaa_id_activity', 'label','code', 'apiName', 'code');
        $this->set('gaa_name', 'label','display', 'apiName', 'display');

    }
}

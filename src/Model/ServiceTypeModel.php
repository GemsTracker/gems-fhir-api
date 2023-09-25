<?php


namespace Gems\Api\Fhir\Model;


use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class ServiceTypeModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
    ) {
        parent::__construct('gems__agenda_activities', $metaModelLoader, $sqlRunner, $translate, 'serviceType');
        $metaModel = $this->getMetaModel();
        $metaModel->set('gaa_id_activity', [
            'label' => 'code',
            'apiName' => 'code',
        ]);
        $metaModel->set('gaa_name', [
            'label' => 'display',
            'apiName' => 'display',
        ]);
    }
}

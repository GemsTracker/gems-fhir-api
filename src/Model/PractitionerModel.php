<?php


namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\PractitionerHumanNameTransformer;
use Gems\Api\Fhir\Model\Transformer\PractitionerTelecomTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Type\BooleanType;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class PractitionerModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
    ) {
        parent::__construct('gems__agenda_staff', $metaModelLoader, $sqlRunner, $translate, 'practitioner');

        $metaModel = $this->getMetaModel();

        $this->addLeftTable('gems__staff', ['gas_id_user' => 'gsf_id_user'], 'gsf', true);

        $this->addColumn(new \Zend_Db_Expr('\'Practitioner\''), 'resourceType');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $this->addColumn(new \Zend_Db_Expr("CASE gsf_gender WHEN 'M' THEN 'male' WHEN 'F' THEN 'female' ELSE 'unknown' END"), 'gender');

        $metaModel->set('gas_id_staff', [
            'label' => 'id',
            'apiName' => 'id',
        ]);

        $metaModel->set('gas_active', [
            'label' => 'active',
            'apiName' => 'active',
            'type' => new BooleanType(),
        ]);
        $metaModel->set('name', [
            'label' => 'name',
        ]);
        $metaModel->set('gender', [
            'label' => 'gender',
        ]);
        $metaModel->set('telecom', [
            'label' => 'telecom',
        ]);

        $metaModel->set('family', [
            'label' => 'family',
        ]);
        $metaModel->set('given', [
            'label' => 'given',
        ]);
        $metaModel->set('email', [
            'label' => 'email',
        ]);
        $metaModel->set('phone', [
            'label' => 'phone',
        ]);

        $metaModel->addTransformer(new PractitionerHumanNameTransformer());
        $metaModel->addTransformer(new PractitionerTelecomTransformer());
    }
}

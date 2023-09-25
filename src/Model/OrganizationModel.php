<?php


namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\OrganizationContactTransformer;
use Gems\Api\Fhir\Model\Transformer\OrganizationTelecomTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Type\BooleanType;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class OrganizationModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
    ) {
        parent::__construct('gems__organization', $metaModelLoader, $sqlRunner, $translate, 'organizations');

        $metaModel = $this->getMetaModel();

        $this->addColumn(new Expression('\'Organization\''), 'resourceType');

        $metaModel->set('resourceType', 'label', 'resourceType');

        $metaModel->set('gor_id_organization', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('gor_active', [
            'label' => 'active',
            'apiName' => 'active',
            'type' => new BooleanType(),
        ]);
        $metaModel->set('gor_name', [
            'label' => 'name',
            'apiName' => 'name',
        ]);
        $metaModel->set('telecom', [
            'label' => 'telecom',
        ]);
        $metaModel->set('contact', [
            'label' => 'contact',
        ]);
        $metaModel->set('gor_code', [
            'label' => 'code',
            'apiName' => 'code',
        ]);

        $metaModel->addTransformer(new OrganizationTelecomTransformer());
        $metaModel->addTransformer(new OrganizationContactTransformer());
    }
}

<?php


namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\BooleanTransformer;
use Gems\Api\Fhir\Model\Transformer\LocationAddressTransformer;
use Gems\Api\Fhir\Model\Transformer\LocationStatusTransformer;
use Gems\Api\Fhir\Model\Transformer\LocationTelecomTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Type\BooleanType;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class LocationModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
    ) {
        parent::__construct('gems__locations', $metaModelLoader, $sqlRunner, $translate, 'location');
        $metaModel = $this->getMetaModel();
        $this->addColumn(new Expression('\'Location\''), 'resourceType');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $metaModel->set('glo_id_location', [
            'label' => 'id',
            'apiName' => 'id',
        ]);

        $metaModel->set('glo_active', [
            'label' => 'status',
            'apiName' => 'status',
            'type' => new BooleanType(),
        ]);
        $metaModel->set('glo_name', [
            'label' => 'name',
            'apiName' => 'name',
        ]);
        $metaModel->set('telecom', [
            'label' => 'telecom'
        ]);

        $metaModel->set('address', [
            'label' => 'address'
        ]);
        // Search options
        $metaModel->set('address-city', [
            'label' => 'address-city',
        ]);
        $metaModel->set('address-country', [
            'label' => 'address-country'
        ]);
        $metaModel->set('address-postalcode', [
            'label' => 'address-postalcode',
        ]);

       $metaModel->addTransformer(new LocationStatusTransformer());
       $metaModel->addTransformer(new LocationTelecomTransformer());
       $metaModel->addTransformer(new LocationAddressTransformer());
       $metaModel->addTransformer(new BooleanTransformer(['glo_active']));
    }
}

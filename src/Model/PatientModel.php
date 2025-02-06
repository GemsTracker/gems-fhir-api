<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientHumanNameTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientIdTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientTelecomTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Transform\MaskTransformer;
use Gems\Model\Type\BooleanType;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class PatientModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        MaskRepository $maskRepository,
    ) {
        parent::__construct('gems__respondents', $metaModelLoader, $sqlRunner, $translate, 'respondents');

        $metaModel = $this->getMetaModel();

        $this->addTable('gems__respondent2org', ['grs_id_user' => 'gr2o_id_user']);
        $this->addTable('gems__reception_codes', ['gr2o_reception_code' => 'grc_id_reception_code']);

        $this->addTable('gems__organizations', ['gr2o_id_organization' => 'gor_id_organization'], 'gor', false);

        $this->addColumn(new Expression('CONCAT(gr2o_patient_nr, "@", gr2o_id_organization)'), 'id');
        //$this->addColumn('grc_success', 'active');
        $this->addColumn(new Expression("CASE grs_gender WHEN 'M' THEN 'male' WHEN 'F' THEN 'female' ELSE 'unknown' END"), 'gender');

        $this->addColumn(new Expression('\'Patient\''), 'resourceType');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $metaModel->set('id', [
            'label' => 'id',
        ]);
        $metaModel->set('grc_success', [
            'label' => 'active',
            'apiName' => 'active',
            'type' => new BooleanType(),
        ]);
        $metaModel->set('gender', [
            'label' => 'gender',
        ]);
        $metaModel->set('grs_birthday', [
            'label' => 'birthDate',
            'apiName' => 'birthDate',
        ]);

        $metaModel->set('name', [
            'label' => 'name',
        ]);
        $metaModel->set('gr2o_created', [
            'label' => 'created',
            'apiName' => 'created',
        ]);
        $metaModel->set('gr2o_changed', [
            'label' => 'changed',
            'apiName' => 'changed',
        ]);

        // search options
        $metaModel->set('family', [
            'label' => 'name',
        ]);
        $metaModel->set('given', [
            'label' => 'name',
        ]);

        $metaModel->set('telecom', [
            'label' => 'telecom',
        ]);
        // search options
        $metaModel->set('email', [
            'label' => 'email',
        ]);
        $metaModel->set('phone', [
            'label' => 'phone',
        ]);

        $metaModel->set('managingOrganization', [
            'label' => 'managingOrganization',
        ]);
        // search options
        $metaModel->set('organization', [
            'label' => 'organization',
        ]);
        $metaModel->set('organization_name', [
            'label' => 'organization_name',
        ]);
        $metaModel->set('organization_code', [
            'label' => 'organization_code',
        ]);

        $metaModel->addTransformer(new MaskTransformer($maskRepository));
        $metaModel->addTransformer(new PatientIdTransformer());
        $metaModel->addTransformer(new PatientHumanNameTransformer());
        $metaModel->addTransformer(new PatientTelecomTransformer());
        $metaModel->addTransformer(new ManagingOrganizationTransformer('gr2o_id_organization', true));
    }
}

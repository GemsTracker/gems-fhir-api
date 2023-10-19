<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\EpisodeOfCarePeriodTransformer;
use Gems\Api\Fhir\Model\Transformer\EpisodeOfCareStatusTransformer;
use Gems\Api\Fhir\Model\Transformer\ManagingOrganizationTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Transform\MaskTransformer;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use MUtil\Model\Type\JsonData;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class EpisodeOfCareModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        MaskRepository $maskRepository,
    ) {
        parent::__construct('gems__episodes_of_care', $metaModelLoader, $sqlRunner, $translate, 'episodesOfCare');
        $metaModel = $this->getMetaModel();

        $this->addColumn(new Expression('\'EpisodeOfCare\''), 'resourceType');

        $metaModel->set('resourceType', [
            'label' => 'resourceType',
        ]);

        $this->addTable('gems__respondent2org', ['gec_id_user' => 'gr2o_id_user', 'gec_id_organization' => 'gr2o_id_organization']);
        $this->addTable('gems__organizations', ['gec_id_organization' => 'gor_id_organization']);

        $metaModel->addTransformer(new MaskTransformer($maskRepository));

        $metaModel->set('gec_episode_of_care_id', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('gec_status', [
            'label' => 'status',
            'apiName' => 'status',
        ]);
        $metaModel->set('patient', [
            'label' => 'patient',
            'apiName' => 'patient',
        ]);
        $metaModel->set('period', [
            'label' => 'period',
            'apiName' => 'period',
        ]);
        $metaModel->set('managingOrganization', [
            'label' => 'managingOrganization',
            'apiName' => 'managingOrganization',
        ]);

        /*$metaModel->set('gec_extra_data', [
           'type' => $me
        ]);*/

        /*$jsonType = new JsonData(10);
        $jsonType->apply($this, 'gec_diagnosis_data', false);
        $jsonType->apply($this, 'gec_extra_data',     false);*/

        $metaModel->addTransformer(new EpisodeOfCareStatusTransformer());
        $metaModel->addTransformer(new EpisodeOfCarePeriodTransformer());
        $metaModel->addTransformer(new PatientReferenceTransformer('patient'));
        $metaModel->addTransformer(new ManagingOrganizationTransformer('gec_id_organization', true));

        $maskRepository->applyMaskToDataModel($metaModel);
    }
}

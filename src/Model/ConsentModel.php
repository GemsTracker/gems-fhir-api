<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\ConsentCategoryTransformer;
use Gems\Api\Fhir\Model\Transformer\ConsentControllerTransformer;
use Gems\Api\Fhir\Model\Transformer\ConsentDecisionTransformer;
use Gems\Api\Fhir\Model\Transformer\ConsentStatusTransformer;
use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Transform\MaskTransformer;
use Gems\User\Mask\MaskRepository;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class ConsentModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
        MaskRepository $maskRepository,
    )
    {
        parent::__construct('gems__respondent2org', $metaModelLoader, $sqlRunner, $translate, 'respondentConsent');
        $this->addTable('gems__consents', ['gco_description' => 'gr2o_consent']);
        $this->addTable('gems__respondents', ['gr2o_id_user' => 'grs_id_user']);
        $this->addTable('gems__organizations', ['gr2o_id_organization' => 'gor_id_organization']);

        $this->addColumn(new Expression('CONCAT(gr2o_patient_nr, "@", gr2o_id_organization)'), 'id');
        $this->addColumn(new Expression('\'Consent\''), 'resourceType');
        $this->addColumn(new Expression("CASE gco_description 
            WHEN 'Unknown' THEN 'unknown'
            ELSE 'active' END"), 'status');

        $this->addColumn(new Expression("CASE gco_description 
            WHEN 'Yes' THEN 'permit'
            WHEN 'No' THEN 'deny'
            ELSE null END"), 'decision');

        $this->metaModel->addTransformer(new PatientReferenceTransformer('subject'));
        $this->metaModel->addTransformer(new ConsentDecisionTransformer());
        $this->metaModel->addTransformer(new ConsentControllerTransformer());
        $this->metaModel->addTransformer(new ConsentCategoryTransformer());

        $this->metaModel->set('subject', [
            'label' => 'subject',
        ]);
        $this->metaModel->set('patient', [
            'label' => 'patient',
        ]);

        $this->metaModel->set('controller', [
            'label' => 'controller',
        ]);
        $this->metaModel->set('category', [
            'label' => 'category',
        ]);

        $this->metaModel->addTransformer(new MaskTransformer($maskRepository));
    }
}
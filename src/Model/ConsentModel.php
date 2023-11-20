<?php

namespace Gems\Api\Fhir\Model;

use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
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

        $this->addColumn(new Expression('CONCAT(gr2o_patient_nr, "@", gr2o_id_organization)'), 'id');
        $this->addColumn(new Expression('\'Consent\''), 'resourceType');
        $this->addColumn(new Expression('\'active\''), 'status');

        $this->addColumn(new Expression("CASE gco_code WHEN 'consent given' THEN 'permit' ELSE 'deny' END"), 'decision');

        $this->metaModel->addTransformer(new PatientReferenceTransformer('subject'));

        $this->metaModel->set('subject', [
            'label' => 'subject',
        ]);
        $this->metaModel->set('patient', [
            'label' => 'patient',
        ]);
    }
}
<?php

namespace Gems\Api\Fhir\Model;


use Gems\Api\Fhir\Model\Transformer\PatientReferenceTransformer;
use Gems\Api\Fhir\Model\Transformer\RelatedPersonHumanNameTransformer;
use Gems\Api\Fhir\Model\Transformer\RelatedPersonTelecomTransformer;
use Gems\Model\GemsJoinModel;
use Gems\Model\MetaModelLoader;
use Gems\Model\Type\BooleanType;
use Laminas\Db\Sql\Expression;
use Zalt\Base\TranslatorInterface;
use Zalt\Model\Sql\SqlRunnerInterface;

class RelatedPersonModel extends GemsJoinModel
{
    public function __construct(
        MetaModelLoader $metaModelLoader,
        SqlRunnerInterface $sqlRunner,
        TranslatorInterface $translate,
    ) {
        parent::__construct('gems__respondent_relations', $metaModelLoader, $sqlRunner, $translate, 'relatedPerson');
        $metaModel = $this->getMetaModel();

        $this->addTable('gems__respondents', ['grr_id_respondent' => 'grs_id_user']);
        $this->addTable('gems__respondent2org', ['grs_id_user' => 'gr2o_id_user']);

        $this->addColumn(new Expression('\'RelatedPerson\''), 'resourceType');
        $this->addColumn(new Expression("CASE grr_gender WHEN 'M' THEN 'male' WHEN 'F' THEN 'female' ELSE 'unknown' END"), 'gender');

        $metaModel->set('resourceType', [
            'label' => 'resourceType'
        ]);

        $metaModel->set('grr_id_relation', [
            'label' => 'id',
            'apiName' => 'id',
        ]);
        $metaModel->set('grr_active', [
            'label' => 'active',
            'apiName' => 'active',
            'type' => new BooleanType(),
        ]);

        $metaModel->set('patient', [
            'label' => 'patient',
        ]);

        // Relationship according to official specs should be a set code as in https://www.hl7.org/fhir/valueset-relatedperson-relationshiptype.html
        // For now the relation type used.
        $metaModel->set('grr_type', [
            'label' => 'relationship',
            'apiName' => 'relationship',
        ]);

        $metaModel->set('name', [
            'label' => 'name',
        ]);
        $metaModel->set('telecom', [
            'label' => 'telecom',
        ]);

        $metaModel->set('gender', [
            'label' => 'gender',
        ]);
        $metaModel->set('grr_birthdate', [
            'label' => 'birthdate',
            'apiName' => 'birthdate',
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


        $metaModel->addTransformer(new PatientReferenceTransformer('patient'));
        $metaModel->addTransformer(new RelatedPersonHumanNameTransformer());
        $metaModel->addTransformer(new RelatedPersonTelecomTransformer());
    }
}

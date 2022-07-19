<?php


namespace Gems\Api\Fhir\Model\Transformer;

class QuestionnaireTaskForTransformer extends PatientReferenceTransformer
{
    protected string $fieldName = 'for';
}

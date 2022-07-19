<?php

namespace Gems\Api\Fhir;


class Endpoints
{
    public const PREFIX = 'fhir';

    public const APPOINTMENT = 'fhir/appointment/';

    public const CARE_PLAN = 'fhir/care-plan/';

    public const EPISODE_OF_CARE = 'fhir/episode-of-care/';

    public const LOCATION = 'fhir/location/';

    public const ORGANIZATION = 'fhir/organization/';

    public const PATIENT = 'fhir/patient/';

    public const PRACTITIONER = 'fhir/practitioner/';

    public const RELATED_PERSON = 'fhir/related-person/';

    public const QUESTIONNAIRE = 'fhir/questionnaire/';

    public const QUESTIONNAIRE_TASK = 'fhir/questionnaire-task/';

    public const TREATMENT = 'fhir/treatment/';

    public static function getEndpointByResourceType(string $resourceType): ?string
    {
        switch (strtolower($resourceType)) {
            case 'appointment':
                return static::APPOINTMENT;
            case 'careplan':
                return static::CARE_PLAN;
            case 'episodeofcare':
                return static::EPISODE_OF_CARE;
            case 'location':
                return static::LOCATION;
            case 'organization':
                return static::ORGANIZATION;
            case 'patient':
                return static::PATIENT;
            case 'practitioner':
                return static::PRACTITIONER;
            case 'relatedperson':
                return static::RELATED_PERSON;
            case 'questionnaire':
                return static::QUESTIONNAIRE;
            case 'questionnairetask':
                return static::QUESTIONNAIRE_TASK;
            case 'treatment':
                return static::TREATMENT;
        }

        return null;
    }
}

<?php


namespace Gems\Api\Fhir;


use Gems\Api\Fhir\Model\AppointmentModel;
use Gems\Api\Fhir\Model\CarePlanModel;
use Gems\Api\Fhir\Model\EpisodeOfCareModel;
use Gems\Api\Fhir\Model\LocationModel;
use Gems\Api\Fhir\Model\OrganizationModel;
use Gems\Api\Fhir\Model\PatientModel;
use Gems\Api\Fhir\Model\PractitionerModel;
use Gems\Api\Fhir\Model\QuestionnaireModel;
use Gems\Api\Fhir\Model\QuestionnaireResponseModel;
use Gems\Api\Fhir\Model\QuestionnaireTaskModel;
use Gems\Api\Fhir\Model\RelatedPersonModel;
use Gems\Api\Fhir\Model\ServiceTypeModel;
use Gems\Api\RestModelConfigProviderAbstract;

class ConfigProvider extends RestModelConfigProviderAbstract
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRoutes(),
        ];
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function getRestModels(): array
    {
        return [
            'fhir/patient' => [
                'model' => PatientModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'active',
                    'gender',
                    'birthDate',
                    'name',
                    'telecom',
                    'managingOrganization',
                    'created',
                    'changed',
                ],
                'idField' => 'id',
                'idFieldRegex' => '[A-Za-z0-9\-@]+',
                'patientIdField' => 'id',
            ],
            'fhir/appointment' => [
                'model' => AppointmentModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'start',
                    'end',
                    'created',
                    'comment',
                    'description',
                    'serviceType',
                    'participant',
                    'created',
                    'changed',
                ],
                'idField' => 'id',
                'patientIdField' => 'patient',
            ],
            'fhir/episode-of-care' => [
                'model' => EpisodeOfCareModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'start',
                    'end',
                    'created',
                    'comment',
                    'description',
                    'serviceType',
                    'participant',
                ],
                'idField' => 'id',
                'patientIdField' => 'patient',
            ],
            'fhir/location' => [
                'model' => LocationModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'name',
                    'telecom',
                    'address',
                ],
            ],
            'fhir/organization' => [
                'model' => OrganizationModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'active',
                    'name',
                    'code',
                    'telecom',
                    'contact',
                ],
            ],
            'fhir/practitioner' => [
                'model' => PractitionerModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'active',
                    'name',
                    'gender',
                    'telecom',
                ],
            ],
            'fhir/related-person' => [
                'model' => RelatedPersonModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'active',
                    'relationship',
                    'name',
                    'gender',
                    'telecom',
                    'birthdate',
                ],
            ],

            'fhir/questionnaire' => [
                'model' => QuestionnaireModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'name',
                    'date',
                    'description',
                    'subjectType',
                    'item',
                ],
            ],
            'fhir/questionnaire-task' => [
                'model' => QuestionnaireTaskModel::class,
                'methods' => ['GET', 'PATCH'],
                'idFieldRegex' => '[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}',
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'completedAt',
                    'priority',
                    'intent',
                    'owner',
                    'for',
                    'authoredOn',
                    'lastModified',
                    'executionPeriod',
                    'managingOrganization',
                    'focus',
                    'info',
                    'carePlanSuccess',
                ],
                'allowed_save_fields' => [
                    'executionPeriod',
                    'status',
                    'gto_id_token',
                    'gto_id_respondent_track',
                    'gto_id_round',
                    'gto_id_track',
                    'gto_id_survey',
                ],
                'patientIdField' => [
                    'for',
                    'patient',
                ],
            ],
            'fhir/questionnaire-response' => [
                'model' => QuestionnaireResponseModel::class,
                'methods' => ['GET'],
                'idFieldRegex' => '[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}',
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'authored',
                    'status',
                    'subject',
                    'source',
                    'author',
                    'item',
                ],
                'patientIdField' => [
                    'patient',
                    'subject'
                ],
            ],
            'fhir/care-plan' => [
                'model' => CarePlanModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'resourceType',
                    'id',
                    'status',
                    'intent',
                    'title',
                    'code',
                    'created',
                    'subject',
                    'period',
                    'contributor',
                    'supportingInfo',
                    'activity',
                ],
                'patientIdField' => [
                    'patient',
                    'subject'
                ],
            ],
            'fhir/codesystem/service-type' => [
                'model' => ServiceTypeModel::class,
                'methods' => ['GET'],
                'allowed_fields' => [
                    'code',
                    'display',
                    'name',
                    'telecom',
                    'contact',
                ],
                'idField' => 'code',
            ],
        ];
    }

    public function getRoutes(bool $includeModelRoutes = true): array
    {
        $modelRoutes = parent::getRoutes($includeModelRoutes);

        $routes = [];

        return array_merge($routes, $modelRoutes);
    }

}

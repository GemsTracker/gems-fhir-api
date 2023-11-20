<?php


namespace Gems\Api\Fhir;


use Gems\Api\Fhir\Model\AppointmentModel;
use Gems\Api\Fhir\Model\CarePlanModel;
use Gems\Api\Fhir\Model\ConsentModel;
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
    public function __construct(protected string $pathPrefix = '/api/fhir')
    {}

    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return mixed[]
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes' => $this->routeGroup([
                'path' => $this->pathPrefix,
                'middleware' => $this->getMiddleware(),
            ],
                $this->getRoutes()
            ),
        ];
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * @param bool $includeModelRoutes
     * @return mixed[]
     */
    public function getRoutes(bool $includeModelRoutes = true): array
    {
        return [
            ...$this->createModelRoute(
                endpoint: 'patient',
                model: PatientModel::class,
                methods: ['GET'],
                allowedFields: [
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
                idField: 'id',
                idRegex: '[A-Za-z0-9\-@]+',
                patientIdField: 'id',
            ),
            ...$this->createModelRoute(
                endpoint: 'appointment',
                model: AppointmentModel::class,
                methods: ['GET'],
                allowedFields: [
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
                idField: 'id',
                patientIdField: 'patient',
            ),
            ...$this->createModelRoute(
                endpoint: 'episode-of-care',
                model: EpisodeOfCareModel::class,
                methods: ['GET'],
                allowedFields: [
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
                idField: 'id',
                patientIdField: 'patient',
            ),
            ...$this->createModelRoute(
                endpoint: 'location',
                model: LocationModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'status',
                    'name',
                    'telecom',
                    'address',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'organization',
                model: OrganizationModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'active',
                    'name',
                    'code',
                    'telecom',
                    'contact',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'practitioner',
                model: PractitionerModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'active',
                    'name',
                    'gender',
                    'telecom',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'related-person',
                model: RelatedPersonModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'active',
                    'relationship',
                    'name',
                    'gender',
                    'telecom',
                    'birthdate',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'questionnaire',
                model: QuestionnaireModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'status',
                    'name',
                    'date',
                    'description',
                    'subjectType',
                    'item',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'questionnaire-task',
                model: QuestionnaireTaskModel::class,
                methods: ['GET', 'PATCH'],
                idRegex: '[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}',
                allowedFields: [
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
                allowedSaveFields: [
                    'executionPeriod',
                    'status',
                    'gto_id_token',
                    'gto_id_respondent_track',
                    'gto_id_round',
                    'gto_id_track',
                    'gto_id_survey',
                ],
                patientIdField: [
                    'for',
                    'patient',
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'questionnaire-response',
                model: QuestionnaireResponseModel::class,
                methods: ['GET'],
                idRegex: '[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}',
                allowedFields: [
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
                patientIdField: [
                    'patient',
                    'subject'
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'care-plan',
                model: CarePlanModel::class,
                methods: ['GET'],
                allowedFields: [
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
                    'activity'
                ],
                patientIdField: [
                    'patient',
                    'subject'
                ],
            ),
            ...$this->createModelRoute(
                endpoint: 'codesystem/service-type',
                model: ServiceTypeModel::class,
                methods: ['GET'],
                allowedFields: [
                    'code',
                    'display',
                    'name',
                    'telecom',
                    'contact',
                ],
                idField: 'code',
            ),

            ...$this->createModelRoute(
                endpoint: 'consent',
                model: ConsentModel::class,
                methods: ['GET'],
                allowedFields: [
                    'resourceType',
                    'id',
                    'status',
                    'decision',
                    'subject',
                    'controller',
                    'category',
                ],
                idField: 'id',
                idRegex: '[A-Za-z0-9\-@]+',
                patientIdField: 'id',
            ),
        ];
    }

}

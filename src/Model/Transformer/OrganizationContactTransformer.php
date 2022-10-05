<?php


namespace Gems\Api\Fhir\Model\Transformer;

use MUtil\Model\ModelAbstract;
use MUtil\Model\ModelTransformerAbstract;

class OrganizationContactTransformer extends ModelTransformerAbstract
{
    /**
     * The transform function performs the actual transformation of the data and is called after
     * the loading of the data in the source model.
     *
     * @param ModelAbstract $model The parent model
     * @param array $data Nested array
     * @param boolean $new True when loading a new item
     * @param boolean $isPostData With post data, unselected multiOptions values are not set so should be added
     * @return array Nested array containing (optionally) transformed data
     */
    public function transformLoad(ModelAbstract $model, array $data, $new = false, $isPostData = false): array
    {
        foreach($data as $key=>$item) {

            $contact = [
                'purpose' => [
                    'coding' => [
                        'system' => 'https://terminology.hl7.org/CodeSystem/contactentity-type',
                        'code' => 'ADMIN',
                    ],
                ],
            ];

            if (isset($item['gor_contact_name'])) {
                $contact['name']['text'] = $item['gor_contact_name'];
            }
            if (isset($item['gor_contact_email'])) {

                $contact['telecom'][] = [
                    'system' => 'email',
                    'value' => $item['gor_contact_email'],
                ];

                // Only add when email is known
                $data[$key]['contact'][] = $contact;
            }
        }

        return $data;
    }
}

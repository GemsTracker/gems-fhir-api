<?php

namespace Gems\Api\Fhir\Model\Transformer;


use Gems\Tracker\TrackerInterface;
use Zalt\Model\MetaModelInterface;
use MUtil\Model\ModelTransformerAbstract;

class QuestionnaireResponseItemsTransformer extends ModelTransformerAbstract
{
    /**
     * @var string Language
     */
    protected string $language;

    /**
     * @var TrackerInterface
     */
    protected TrackerInterface $tracker;

    public function __construct(TrackerInterface $tracker, string $language)
    {
        $this->tracker = $tracker;
        $this->language = $language;
    }

    protected function getItemsFromAnswers(array $tokenAnswers, array $surveyInformation): array
    {
        $items = [];

        foreach($tokenAnswers as $answers) {
            $tokenItems = [];
            foreach($answers as $key=>$answer) {
                if(!isset($surveyInformation[$key])) {
                    continue;
                }
                $answerItem = [
                    'linkId' => $key,
                    'text' => $surveyInformation[$key]['question'],
                ];

                if (isset($surveyInformation[$key]['answers']) && is_array($surveyInformation[$key]['answers']) &&
                    isset($surveyInformation[$key]['answers'][$answer])) {

                    $display = $surveyInformation[$key]['answers'][$answer];
                    if (is_numeric($display)) {
                        $display = +$display;
                    }

                    $answerItem['answer']['d'] = [
                        'code' => $answer,
                        'display' => $display,
                        'system' => null, // Should reference a place to view the answer options
                    ];
                } elseif ($answer === null) {
                    $answerItem['answer'] = null;
                } else {
                    switch ($surveyInformation[$key]['type']) {
                        case 'N':
                        case 'K':
                            if ((int)$answer == $answer) {
                                $answerItem['answer']['valueInteger'] = (int)$answer;
                            } else {
                                $answerItem['answer']['valueDecimal'] = (float)$answer;
                            }
                            break;
                        default:
                            $answerItem['answer']['valueString'] = str_replace('\n', "\n", $answer);
                    }
                }

                $tokenItems[] = $answerItem;
            }

            $items[$answers['token']] = $tokenItems;
        }

        return $items;
    }

    public function transformLoad(MetaModelInterface $model, array $data, $new = false, $isPostData = false): array
    {
        $tokensBySource = [];
        $sourceSurveyIds = [];
        foreach($data as $row) {
            $tokensBySource[$row['gsu_id_source']][$row['gto_id_survey']][] = $row['gto_id_token'];
            $sourceSurveyIds[$row['gto_id_survey']] = $row['gsu_surveyor_id'];
        }

        $tokenItems = [];

        foreach ($tokensBySource as $sourceId => $tokensBySurvey) {
            $source = $this->tracker->getSource($sourceId);
            foreach ($tokensBySurvey as $surveyId => $tokenIds) {
                $answers = $source->getRawTokenAnswerRows(['token' => $tokenIds], $surveyId, $sourceSurveyIds[$surveyId]);
                $surveyInformation = $source->getQuestionInformation($this->language, $surveyId, $sourceSurveyIds[$surveyId]);
                $tokenItems = array_merge($tokenItems, $this->getItemsFromAnswers($answers, $surveyInformation));
            }
        }

        foreach ($data as $key => $row) {
            $tokenId = str_replace('-', '_', $row['gto_id_token']);
            if (isset($tokenItems[$tokenId])) {
                $data[$key]['item'] = $tokenItems[$tokenId];
            }
        }

        return $data;
    }
}

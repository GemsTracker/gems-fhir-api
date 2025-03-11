<?php

declare(strict_types=1);

namespace GemsFhirApiTest\Model\Transformer;

use Gems\Api\Fhir\Model\Transformer\PatientHumanNameTransformer;
use Gems\Db\ResultFetcher;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Expression;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zalt\Model\MetaModelInterface;

class PatientHumanNameTransformerTest extends TestCase
{
    private function getMetaModel(): MetaModelInterface
    {
        return $this->createMock(MetaModelInterface::class);
    }

    private function getTransformer(): PatientHumanNameTransformer
    {
        $platform = $this->createMock(PlatformInterface::class);
        $platform->method('quoteValue')->willReturnCallback(function($value) {
            return "'$value'";
        });

        $resultFetcher = $this->createMock(ResultFetcher::class);

        $resultFetcher->method('getPlatform')->willReturn($platform);

        return new PatientHumanNameTransformer($resultFetcher);
    }

    public static function transformLoadDataProvider(): iterable
    {
        yield 'just last name' => [
            [
                [
                    'grs_last_name' => 'Janssen',
                ],
            ],
            [
                [
                    'name' => [
                        [
                            'family' => 'Janssen',
                        ],
                    ],
                    'grs_last_name' => 'Janssen',
                ],
            ]
        ];

        yield 'surname prefix' => [
            [
                [
                    'grs_last_name' => 'Jong',
                    'grs_surname_prefix' => 'de',
                ],
            ],
            [
                [
                    'name' => [
                        [
                            'family' => 'de Jong',
                        ],
                    ],
                    'grs_last_name' => 'Jong',
                    'grs_surname_prefix' => 'de',
                ],
            ]
        ];

        yield 'first name' => [
            [
                [
                    'grs_last_name' => 'Janssen',
                    'grs_first_name' => 'Janneke'
                ],
            ],
            [
                [
                    'name' => [
                        [
                            'family' => 'Janssen',
                            'given' => [
                                [
                                    'value' => "Janneke",
                                    'extension' => [
                                        [
                                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                                            'valueCode' => 'LS',
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'grs_last_name' => 'Janssen',
                    'grs_first_name' => 'Janneke'
                ],
            ]
        ];

        yield 'initials' => [
            [
                [
                    'grs_last_name' => 'Janssen',
                    'grs_initials_name' => 'J.'
                ],
            ],
            [
                [
                    'name' => [
                        [
                            'family' => 'Janssen',
                            'given' => [
                                [
                                    'value' => "J.",
                                    'extension' => [
                                        [
                                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                                            'valueCode' => 'IN',
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'grs_last_name' => 'Janssen',
                    'grs_initials_name' => 'J.'
                ],
            ]
        ];

        yield 'first name and initials' => [
            [
                [
                    'grs_last_name' => 'Janssen',
                    'grs_first_name' => 'Janneke',
                    'grs_initials_name' => 'J.'
                ],
            ],
            [
                [
                    'name' => [
                        [
                            'family' => 'Janssen',
                            'given' => [
                                [
                                    'value' => "Janneke",
                                    'extension' => [
                                        [
                                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                                            'valueCode' => 'LS',
                                        ]
                                    ]
                                ],
                                [
                                    'value' => "J.",
                                    'extension' => [
                                        [
                                            'url' => 'http://hl7.org/fhir/StructureDefinition/iso21090-EN-qualifier',
                                            'valueCode' => 'IN',
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'grs_last_name' => 'Janssen',
                    'grs_first_name' => 'Janneke',
                    'grs_initials_name' => 'J.'
                ],
            ]
        ];
    }

    #[DataProvider('transformLoadDataProvider')]
    public function testTransformLoadName(array $row, $expected): void
    {
        $transformer = $this->getTransformer();
        $result = $transformer->transformLoad($this->getMetaModel(), $row);

        $this->assertEquals($expected, $result);
    }

    public function testNameFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'name' => 'some search string',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $expected = [
            [
                'grs_first_name' => 'some search string',
                'grs_initials_name' => 'some search string',
                'grs_last_name' => 'some search string',
                'grs_surname_prefix' => 'some search string',
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testFamilyFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'family' => 'Janssen',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);
        $this->assertInstanceOf(Expression::class, $result[0]);

        if ($result[0] instanceof Expression) {
            $this->assertEquals(
                "CONCAT_WS(' ', grs_surname_prefix, grs_last_name) LIKE '%Janssen%'",
                $result[0]->getExpression()
            );
        }
    }

    public function testGivenFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'given' => 'Janneke',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $this->assertEquals(
            "(grs_first_name LIKE '%Janneke%')
             OR (grs_initials_name LIKE '%Janneke%')",
            $result[0]
        );
    }

}
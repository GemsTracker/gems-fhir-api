<?php

declare(strict_types=1);

namespace Model\Transformer;

use Gems\Api\Fhir\Model\Transformer\PatientTelecomTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zalt\Model\MetaModelInterface;

#[CoversClass(PatientTelecomTransformer::class)]
class PatientTelecomTransformerTest extends TestCase
{
    private function getMetaModel(): MetaModelInterface
    {
        return $this->createMock(MetaModelInterface::class);
    }

    private function getTransformer(): PatientTelecomTransformer
    {
        return new PatientTelecomTransformer();
    }

    public function testNothing(): void
    {
        $transformer = $this->getTransformer();

        $data = [
            [
                'someRandomField' => true,
            ],
        ];

        $result = $transformer->transformLoad($this->getMetaModel(), $data);

        $data = [
            [
                'someRandomField' => true,
                'telecom' => [],
            ],
        ];

        $this->assertEquals($data, $result);
    }

    public function testLoadEmail(): void
    {
        $transformer = $this->getTransformer();

        $data = [
            [
                'gr2o_email' => 'janneke@janssen.test',
            ],
        ];

        $result = $transformer->transformLoad($this->getMetaModel(), $data);

        $expected = [
            [
                'gr2o_email' => 'janneke@janssen.test',
                'telecom' => [
                    [
                        'system' => 'email',
                        'value' => 'janneke@janssen.test',
                        'rank' => 100,
                    ]
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testLoadEmailWithMailable(): void
    {
        $transformer = $this->getTransformer();

        $data = [
            [
                'gr2o_email' => 'janneke@janssen.test',
                'gr2o_mailable' => 50,
            ],
        ];

        $result = $transformer->transformLoad($this->getMetaModel(), $data);

        $expected = [
            [
                'gr2o_email' => 'janneke@janssen.test',
                'gr2o_mailable' => 50,
                'telecom' => [
                    [
                        'system' => 'email',
                        'value' => 'janneke@janssen.test',
                        'rank' => 51,
                    ]
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public static function phoneNumberDataProvider(): iterable
    {
        yield 'phone home' => [
            [
                'grs_phone_1' => '+31612345678',
            ],
            [
                'grs_phone_1' => '+31612345678',
                'telecom' => [
                    [
                        'system' => 'phone',
                        'value' => '+31612345678',
                        'use' => 'home',
                    ]
                ]
            ],
        ];

        yield 'phone work' => [
            [
                'grs_phone_2' => '+31612345678',
            ],
            [
                'grs_phone_2' => '+31612345678',
                'telecom' => [
                    [
                        'system' => 'phone',
                        'value' => '+31612345678',
                        'use' => 'work',
                    ]
                ]
            ],
        ];

        yield 'phone mobile' => [
            [
                'grs_phone_3' => '+31612345678',
            ],
            [
                'grs_phone_3' => '+31612345678',
                'telecom' => [
                    [
                        'system' => 'phone',
                        'value' => '+31612345678',
                        'use' => 'mobile',
                    ]
                ]
            ],
        ];
    }

    #[DataProvider('phoneNumberDataProvider')]
    public function testLoadPhoneNumber(array $row, array $expected): void
    {
        $transformer = $this->getTransformer();

        $result = $transformer->transformLoad($this->getMetaModel(), [$row]);

        $this->assertEquals($expected, $result[0]);
    }

    public function testEmailFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'email' => 'janneke@janssen.test',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $expected = [
            'gr2o_email' => 'janneke@janssen.test',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPhoneFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'phone' => '+31612345678',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $expected = [
            [
                'grs_phone_1' => '+31612345678',
                'grs_phone_2' => '+31612345678',
                'grs_phone_3' => '+31612345678',
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
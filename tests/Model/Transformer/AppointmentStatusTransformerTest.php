<?php

namespace GemsFhirApiTest\Model\Transformer;

use Gems\Api\Fhir\Model\Transformer\AppointmentStatusTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zalt\Model\MetaModel;

#[CoversClass(AppointmentStatusTransformer::class)]
class AppointmentStatusTransformerTest extends TestCase
{
    private function getTransformer(): AppointmentStatusTransformer
    {
        return new AppointmentStatusTransformer();
    }

    private function getMetaModel(): MetaModel
    {
        return $this->createMock(MetaModel::class);
    }

    public static function translateStatusProvider(): iterable
    {
        yield 'aborted' => [
        [
            [
                'gap_status' => 'AB'
            ],
        ],
        [
            [
                'gap_status' => 'cancelled',
            ],
        ],
    ];
        yield 'booked' => [
            [
                [
                    'gap_status' => 'AC'
                ],
            ],
            [
                [
                    'gap_status' => 'booked',
                ],
            ],
        ];
        yield 'cancelled' => [
            [
                [
                    'gap_status' => 'CA'
                ],
            ],
            [
                [
                    'gap_status' => 'cancelled',
                ],
            ],
        ];
        yield 'fulfilled' => [
            [
                [
                    'gap_status' => 'CO'
                ],
            ],
            [
                [
                    'gap_status' => 'fulfilled',
                ],
            ],
        ];
    }


    #[DataProvider('translateStatusProvider')]
    public function testTranslateStatus($data, $expected): void
    {
        $transformer = $this->getTransformer();

        $result = $transformer->transformLoad($this->getMetaModel(), $data);

        $this->assertSame($expected, $result);
    }

    public function testSingleStatusFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'gap_status' => 'cancelled',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $expected = [
            'gap_status' => ['AB', 'CA'],
        ];

        $this->assertEquals($expected, $result);

        $filter = [
            'gap_status' => 'fulfilled',
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);

        $expected = [
            'gap_status' => 'CO',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testArraySingleEntryStatusFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'gap_status' => ['cancelled'],
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);
        $expected = [
            'gap_status' => ['AB', 'CA'],
        ];
        $this->assertEquals($expected, $result);

        $filter = [
            'gap_status' => ['fulfilled'],
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);
        $expected = [
            'gap_status' => ['CO'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testArrayMultipleEntryStatusFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'gap_status' => ['cancelled', 'fulfilled'],
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);
        $expected = [
            'gap_status' => ['AB', 'CA', 'CO'],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testNonExistingEntryStatusFilter(): void
    {
        $transformer = $this->getTransformer();

        $filter = [
            'gap_status' => ['somethingNonExisting'],
        ];

        $result = $transformer->transformFilter($this->getMetaModel(), $filter);
        $expected = [];
        $this->assertEquals($expected, $result);
    }

}
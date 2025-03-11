<?php

declare(strict_types=1);

namespace Model\Transformer;

use Gems\Api\Fhir\Model\Transformer\AllNumberTransformer;
use PHPUnit\Framework\TestCase;
use Zalt\Model\MetaModelInterface;

class AllNumberTransformerTest extends TestCase
{
    private function getMetaModel(): MetaModelInterface
    {
        return $this->createMock(MetaModelInterface::class);
    }

    private function getTransformer(): AllNumberTransformer
    {
        return new AllNumberTransformer();
    }

    public function testNumberStringsBecomeNumbers(): void
    {
        $transformer = $this->getTransformer();

        $data = [
            [
                'test' => '123',
                'test2' => '123.456',
                'test3' => 'hello'
            ],
        ];



        $result = $transformer->transformLoad($this->getMetaModel(), $data);

        $firstRow = reset($result);

        $this->assertIsInt($firstRow['test']);

        $this->assertEquals(123, $firstRow['test']);

        $this->assertIsFloat($firstRow['test2']);

        $this->assertEquals(123.456, $firstRow['test2']);

        $this->assertIsString($firstRow['test3']);
        $this->assertEquals('hello', $firstRow['test3']);
    }
}
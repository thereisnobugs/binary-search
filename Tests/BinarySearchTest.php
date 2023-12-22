<?php

namespace BinarySearch\Tests;

use PHPUnit\Framework\TestCase;

class BinarySearchTest extends TestCase
{
    private $searcher = null;

    protected function setUp(): void
    {
        parent::setUp();
        $dataSource = new \BinarySearch\DataSource\FileData(__DIR__ . '/Mixtures/data_source_1.txt');
        $this->searcher = new \BinarySearch\BinarySearch($dataSource);
    }

    /**
     * @dataProvider existsProvider
     */
    public function testExists($search, $result)
    {
        $this->assertEquals(!is_null($this->searcher->search($search)), $result);
    }

    public function existsProvider()
    {
        return [
            ['000001', true],
            ['000010', true],
            ['000011', true],
            ['000013', true],
            ['000013000001', true],
            ['abcd', true],
            ['', true],
            ['0000000', false],
            ['0000001', false],
            ['000002', false],
            ['fde', false],
            ['000012', false],
            ['000015', false],
        ];
    }
}

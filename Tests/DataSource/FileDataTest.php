<?php

namespace BinarySearch\Tests\DataSource;

use BinarySearch\DataSource;

class FileDataTest extends \BinarySearch\Tests\DataSource\DataSourceTest
{
    private $dataSetClear = '/../Mixtures/data_source_0.txt';
    private $dataSetWithOneString = '/../Mixtures/data_source_3.txt';
    private $dataSet = '/../Mixtures/data_source_1.txt';

    public function countProvider()
    {
        return [
            [
                new DataSource\FileData(__DIR__ . $this->dataSetClear),
                0
            ], [
                new DataSource\FileData(__DIR__ . $this->dataSetWithOneString),
                1
            ],
            [
                new DataSource\FileData(__DIR__ . $this->dataSet),
                filesize(__DIR__ . $this->dataSet)
            ]
        ];
    }

    public function moveDataProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 0, ''],
            [$dataSource, 2, '000001'],
            [$dataSource, 10, '000010']
        ];
    }

    public function startPositionProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 0, true],
            [$dataSource, 2, false],
            [$dataSource, 10, false],
            [new DataSource\FileData(__DIR__ . $this->dataSetWithOneString), 0, true]
        ];
    }

    public function endPositionProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 0, false],
            [$dataSource, 50, true],
            [$dataSource, 49, true],
            [new DataSource\FileData(__DIR__ . $this->dataSetWithOneString), 0, true]
        ];
    }

    public function getNextProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 0, '000001'],
            [$dataSource, 2, '000010'],
            [$dataSource, 10, '000011'],
            [$dataSource, 18, '000013'],
            [$dataSource, 26, '000013'],
            [$dataSource, 32, '000013000001'],
            [$dataSource, 44, 'abcd'],
        ];
    }

    public function getPreviusProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 10, '000001'],
            [$dataSource, 18, '000010'],
            [$dataSource, 26, '000011'],
            [$dataSource, 50, '000013000001'],
        ];
    }

    public function moveNegativeProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, -5],
            [$dataSource, -1],
            [$dataSource, 54],
            [$dataSource, 60],
        ];
    }

    public function dataNegativeProvider()
    {
        return [
            [new DataSource\FileData(__DIR__ . $this->dataSetClear)]
        ];
    }

    public function nextNegativeProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 50],
            [new DataSource\FileData(__DIR__ . $this->dataSetWithOneString), 0],
        ];
    }

    public function previusNegativeProvider()
    {
        $dataSource = new DataSource\FileData(__DIR__ . $this->dataSet);

        return [
            [$dataSource, 0],
            [new DataSource\FileData(__DIR__ . $this->dataSetWithOneString), 0],
        ];
    }

}


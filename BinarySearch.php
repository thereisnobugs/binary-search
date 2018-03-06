<?php

namespace BinarySearch;

use BinarySearch\Interfaces\DataSource;

class BinarySearch
{
    /**
     * Тип поиска по полному совпадению
     */
    const TYPE_FULL = 0;

    /**
     * Тип поиска частичное совпадение, от начала строки.
     * Если выражатся языком регулярных выражений, то: ^(pattern).*$
     */
    const TYPE_PART = 1;

    /**
     * Кол-во элементов хранящихся в кэше определения зацикливания
     */
    const LOOP_CHACHE_SIZE = 3;

    /**
     * @var DataSource
     */
    private $dataSource = null;

    /**
     * Направление сортировки исходных данных
     * @var type
     */
    private $sordDirection = null;

    /**
     * Буффер для определения зацикливания
     * @var Array
     */
    private $loopChache = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Ограничение по кол-ву итераций поиска
     * @var Integer
     */
    private $iterationLimit = null;

    /**
     * Текущая итерация поиска
     * @var Integer
     */
    private $iteration = null;

    /**
     * Внимание, данные должны быть отсортированными!
     *
     * @param DataSource $dataSource Источник данных
     */
    public function __construct(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->logger = new \Psr\Log\NullLogger();

        try {
            // Запоминаем первое и последнее значение
            $this->dataSource->moveTo(0);
            $this->firstValue = $this->dataSource->getData();

            $this->dataSource->moveTo($this->dataSource->count() - 1);
            $this->lastValue = $this->dataSource->getData();
        } catch (\BinarySearch\Exceptions\GoingBeyondData $ex) {
            // Ничего страшного на данном этапе
        }
    }

    /**
     * DI Для объекта логирования
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function injectLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Ищет первую позицию искомого в источнике данных. В случае отсутствия искомого, возвращает null
     * @param String $pattern Искомая строка
     * @param Integer $type Тип сравнения, полный или частичный (от начала строки)
     */
    public function search($pattern, $type = self::TYPE_FULL)
    {
        $this->logger->debug('Start search "' . $pattern . '"');

        // Проверяем, что источник данных, не пуст
        if ($this->dataSource->count() == 0) {
            $this->logger->warning('Data source is clean');

            return null;
        }

        // Ищем в границах
        $this->logger->debug('Check first and last value from data source');
        $position = $this->searchInBoundary($pattern, $type = self::TYPE_FULL);
        if ($position !== false) {
            return $position;
        }

        $startOfRange = 0;
        $endOfRange = $this->dataSource->count();
        $moveDirection = 'down';
        $previusValue = null;

        // Очищаем кэш зацикливания для нового поиска
        $this->loopChache = [];
        $this->initIterationsLimit($endOfRange);

        $this->logger->debug('search: ' . $pattern . ', iteration limit: ' . $this->iterationLimit);

        while ($startOfRange < $endOfRange) {
            // Начинаем итерацию поиска
            $this->iteration();
            // Определяем номер позиции в которой будем сравнивать значение
            $position = $this->getSearchingPosition($endOfRange, $startOfRange, $moveDirection);
            $this->logger->debug('checking value at position ' . $position . ' from range [' . $startOfRange . ':' . $endOfRange . ']');

            // Защита от зацикливания
            if ($this->isLoop($position)) {
                $this->logger->notice('Exit becouse is looping. Searched value not find in data source');

                return null;
            }

            // Читаем данные с указаной позиции
            $this->dataSource->moveTo($position);
            $sourceString = $this->dataSource->getData();
            $this->logger->debug('sourceString:[' . $sourceString . '] at position: ' . $position);

            // Сравниваем
            if ($this->compare($sourceString, $pattern, $type)) {
                $this->logger->debug('finded at position: ' . $position . ' at iteration ' . $this->iteration);

                // @todo поднятся до первого
                return $position;
            }

            // Проверяем целостность сортировки в источнике данных
            if (!is_null($previusValue)) {
                $this->checkSortDirection($previusValue, $sourceString, $moveDirection);
            }
            $previusValue = $sourceString;

            // Определяем дальнейшее направление движения
            $moveDirection = $this->calculateMoveDirection($previusValue, $pattern);

            // Проверяем не достигли ли мы конца данных
            if ($this->isEndOfData($moveDirection)) {
                return null;
            }

            // Меняем границы поиска
            if ($moveDirection == 'down') {
                $this->logger->debug('change part [ ] [*]');
                $startOfRange = $position;
            } else {
                $this->logger->debug('change part [*] [ ]');
                $endOfRange = $position;
            }

        }

    }

    private function calculateMoveDirection($previusValue, $pattern)
    {
        if (
            ($pattern > $previusValue && $this->sordDirection == 1)
            ||
            ($pattern < $previusValue && $this->sordDirection == 0)
        ) {
            return 'down';
        } else {
            return 'up';
        }
    }

    private function isEndOfData($moveDirection)
    {
        switch ($moveDirection) {
            case 'down':
                if ($this->dataSource->isEndPosition()) {
                    return true;
                }
                break;

            case 'up':
                if ($this->dataSource->isStartPosition()) {
                    return true;
                }
                break;
        }

        return false;
    }

    private function searchInBoundary($pattern, $type = self::TYPE_FULL)
    {
        // Проверяем первое значение в источнике данных
        $position = 0;
        $this->dataSource->moveTo($position);
        $firstValue = $this->dataSource->getData();

        if ($this->compare($firstValue, $pattern, $type)) {
            return $position;
        }

        // Проверяем последнее значение в источнике данных
        $position = $this->dataSource->count() - 1;
        $this->dataSource->moveTo($position);
        $lastValue = $this->dataSource->getData();

        if ($this->compare($lastValue, $pattern, $type)) {
            return $position;
        }

        if (
            $firstValue === $lastValue
            ||
            $pattern < $firstValue
            ||
            $pattern > $lastValue
        ) {
            $this->logger->debug('Seeking value lies beyond the boundaries of data in DataSource');

            // В источнике данных отсутствует искомое значение
            return null;
        }

        // Автоматически определяем направление сортировки данных в источнике
        $this->sordDirection = $this->isAscSorted($firstValue, $lastValue);

        return false;
    }

    private function isAscSorted($firstValue, $nextValue)
    {
        return $firstValue <= $nextValue;
    }

    /**
     * Во избежании ухода в бесконечный цикл, устанавливаем лимит итераций как корень от кол-ва данных.
     * @param type $endOfRange
     */
    private function initIterationsLimit($endOfRange)
    {
        $this->iterationLimit = ceil(sqrt($endOfRange)) + 2;
        $this->iteration = 0;
    }

    private function iteration()
    {
        $this->iteration++;
        $this->logger->debug('Iteration #' . $this->iteration);

        if ($this->iterationLimit < $this->iteration) {
            $this->logger->critical('Превышена глубина итераций поиска. Источник данных может быть не сортированным.');
            throw new \BinarySearch\Exceptions\Exception('Превышена глубина итераций поиска. Алгоритм возможно содержит баг или источник данных содержит не сортированные строки.');
        }

    }

    /**
     * Определяем номер позиции данных в источнике для итерации.
     * @param Integer $endOfRange Начало диапазона
     * @param Integer $startOfRange Конец диапазона
     * @param String $moveDirection Направление движения (down|up)
     * @return Integer
     */
    protected function getSearchingPosition($endOfRange, $startOfRange, $moveDirection)
    {
        // Берем среднее значение из диапазона
        if ($moveDirection == 'down') {
            return $startOfRange + ceil(($endOfRange - $startOfRange) / 2);
        } else {
            return $startOfRange + floor(($endOfRange - $startOfRange) / 2);
        }
    }

    /**
     * Проверяет корректность сортировки данных в источнике, на основе двух
     * рядом стоящих значений, и сохраненом ранее направлении сортировки источника
     * @param String $firstValue
     * @param String $nextValue
     * @return Void
     * @throws Exceptions\Exception
     */
    private function checkSortDirection($firstValue, $nextValue, $moveDirection)
    {
        if ($moveDirection == 'up') {
            $tmp = $nextValue;
            $nextValue = $firstValue;
            $firstValue = $tmp;
        }

        if ($this->sordDirection == $this->isAscSorted($firstValue, $nextValue)) {
            return;
        }

        throw new Exceptions\Exception('Данные в источнике не сортированы, либо сортировка не совпадает с указанной');
    }

    /**
     * При достижении границ области, в случае не совпадния (отсутсвия искомого)
     * происходит зацикливание между 2мя границами. Данный метод определяет попали
     * ли мы в зацикливание.
     *
     * Метод определения прост, мы просто запоминаем @see self::LOOP_CHACHE_SIZE
     * последних позиций в которых мы побывали, и если снова прыгаем в эту позицию
     * значит мы зациклились.
     * @param Integer $position
     */
    private function isLoop($position)
    {
        if (in_array($position, $this->loopChache)) {
            return true;
        }

        if (count($this->loopChache) >= self::LOOP_CHACHE_SIZE) {
            array_shift($this->loopChache);
        }

        $this->loopChache[] = $position;
    }

    private function compare($sourceString, $pattern, $type)
    {
        if ($type == self::TYPE_PART) {
            $sourceString = substr($sourceString, 0, strlen($pattern));
        }
        $this->logger->debug('compare: [' . $sourceString . '] == [' . $pattern . ']');

        return $sourceString === $pattern;
    }
}
<?php

namespace BinarySearch\DataSource;

use BinarySearch\Exceptions\GoingBeyondData;
use BinarySearch\Interfaces\DataSource;

/**
 * Предназначен для использования в качестве источника данных,
 * файлы (любых размров). Файл не читается в память целиком, благодаря чему
 * можно использовать хоть терабайтные файлы, с минимальной используемой
 * оперативной памятью.
 *
 * Из за особенности алгоритма Бинарного Поиска, нам не важны сами строки с
 * данными как таковые. Используется как размерная величина `count()` длинна файла,
 * перемещение указателя происходит на основе позиции внутри файла. А после перемещения
 * мы находим позиции начала и конца строки.
 */
class FileData implements DataSource
{
    /**
     * Указатель на файл открытый для чтения
     * @var Integer
     */
    private $fileDescriptor = null;

    /**
     * Внутрений указатель начала строки текущей позиции
     * @var Integer
     */
    protected $positionLineStart = null;

    /**
     * Внутрений указатель конца строки текущей позиции
     * @var Integer
     */
    protected $positionLineEnd = null;

    private $fileSize = null;

    public function __construct($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \BinarySearch\Exceptions\ReadFile('Указаный файл не существует');
        }

        if (!is_readable($filepath)) {
            throw new \BinarySearch\Exceptions\ReadFile('Указаный файл не доступен для чтения');
        }

        $this->fileDescriptor = fopen($filepath, 'r');

        if (!$this->fileDescriptor) {
            throw new \BinarySearch\Exceptions\ReadFile('Ошибка открытия файла на чтений');
        }

        $this->fileSize = filesize($filepath);

        // Определяем координаты первой строки
        try {
            $this->moveTo(0);
        } catch (GoingBeyondData $e) {
            // Нулевой файл, ничего страшного.
        }

        // Определяем координаты последнего значения (игнор пустой строки в конце)
        try {
            $this->moveTo($this->fileSize - 1);
            if ($this->getData() == '' && $this->getPrevius() != '') {
                $this->fileSize = $this->getPosition();
            }
            $this->moveTo(0);
        } catch (GoingBeyondData $ex) {
            // Нулевой файл, ничего страшного.
        }
    }

    /**
     * Так как мы не знаем точное кол-во символов в каждой строке и соответственно
     * с точки зрения быстродействия, при работе с большими файлами, не имеем права считать
     * количество строк полным чтением, то основываемся только на размер файла. Для
     * Самого поиска позиция абсолютно не важна
     */
    public function count()
    {
        return $this->fileSize;
    }

    public function moveTo($position)
    {
        if ($position >= $this->count() || $position < 0) {
            throw new GoingBeyondData('Указана позиция за пределами данных "' . $position . '"');
        }

        $this->positionLineEnd = $this->positionLineStart = null;

        // Ставим указатель на указанную позицию
        fseek($this->fileDescriptor, $position);

        // Ищем конец строки
        $str = fgets($this->fileDescriptor);
        $this->positionLineEnd = ftell($this->fileDescriptor);

        // Ищем начало строки
        if ($position == 0) {
            // Мы и так перемещались в начало
            $this->positionLineStart = 0;

            return;
        }

        while (true) {
            if ($position == -1) {
                // Мы в начале файла
                $this->positionLineStart = 0;

                return;
            }
            fseek($this->fileDescriptor, $position);
            $char = fgetc($this->fileDescriptor);

            if ($char == "\n") {
                $this->positionLineStart = $position + 1;

                return;
            }

            $position--;
        }
    }

    public function getData()
    {
        if (is_null($this->positionLineStart)) {
            throw new GoingBeyondData('Указана позиция за пределами данных');
        }
        fseek($this->fileDescriptor, $this->positionLineStart);

        return trim(fgets($this->fileDescriptor));
    }

    public function getNext()
    {
        if ($this->isEndPosition()) {
            throw new GoingBeyondData('Достигнут конец данных');
        }

        $this->moveTo($this->positionLineEnd + 1);

        return $this->getData();
    }

    public function getPrevius()
    {
        if ($this->isStartPosition()) {
            throw new GoingBeyondData('Достигнуто начало данных');
        }

        $this->moveTo($this->positionLineStart - 2);

        return $this->getData();
    }

    public function getPosition()
    {
        return $this->positionLineStart + (int)round(($this->positionLineEnd - $this->positionLineStart) / 2);
    }

    public function isStartPosition()
    {
        return $this->positionLineStart == 0;
    }

    public function isEndPosition()
    {
        return $this->positionLineEnd == $this->fileSize;
    }
}

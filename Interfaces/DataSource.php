<?php

namespace BinarySearch\Interfaces;

use BinarySearch\Exceptions\GoingBeyondData;

interface DataSource
{
    /**
     * Возвращает колличество данных в источнике
     *
     * @return Integer
     */
    public function count();

    /**
     * Перемещает внутрений указатель на данную позицию.
     *
     * @param Integer $position Номер позиции данных
     *
     * @throws GoingBeyondData В случае неверно указаной позиции.
     */
    public function moveTo($position);

    /**
     * Возвращает данные с текущей позиции, куда указывает внутрений
     * указатель.
     *
     * @return String
     *
     * @throws GoingBeyondData В случае отсутствия данных.
     */
    public function getData();

    /**
     * Возвращает следующие за текущей позицией
     * И перемещает внутрений указатель позиции.
     *
     * @return String
     *
     * @throws GoingBeyondData В случае неверно указаной позиции.
     */
    public function getNext();

    /**
     * Возвращает предыдущие от текущей позицией
     * И перемещает внутрений указатель позиции.
     *
     * @return String
     *
     * @throws GoingBeyondData В случае неверно указаной позиции.
     */
    public function getPrevius();

    /**
     * Возвращает текущее значение позиции
     *
     * @return Integer
     */
    public function getPosition();

    /**
     * Возвращает признак того, что внутрений указатель позиции
     * соответствует началу источника данных
     *
     * @return Boolean
     */
    public function isStartPosition();

    /**
     * Возвращает признак того, что внутрений указатель позиции
     * соответствует последнему элементу источника данных
     *
     * @return Boolean
     */
    public function isEndPosition();
}

<?php
namespace BinarySearch\Tests\DataSource;

use BinarySearch\Interfaces\DataSource;

abstract class DataSourceTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider countProvider
	 */
	public function testCount(DataSource $dataSource, $actualValue) {
		$this->assertEquals($dataSource->count(), $actualValue);
	}
	
	abstract public function countProvider();
	
	/**
	 * @dataProvider moveDataProvider
	 */
	public function testMove(DataSource $dataSource, $position, $actualValue) {
		$dataSource->moveTo($position);
		
		$this->assertEquals($dataSource->getData(), $actualValue);
	}
	
	abstract public function moveDataProvider();
	
	/**
	 * @dataProvider startPositionProvider
	 */
	public function testIsStartPosition(DataSource $dataSource, $position, $actualValue) {
		$dataSource->moveTo($position);
		
		$this->assertEquals($dataSource->isStartPosition(), $actualValue);
	}
	
	abstract public function startPositionProvider();
	
	/**
	 * @dataProvider endPositionProvider
	 */
	public function testIsEndPosition(DataSource $dataSource, $position, $actualValue) {
		$dataSource->moveTo($position);
		
		$this->assertEquals($dataSource->isEndPosition(), $actualValue);
	}
	
	abstract public function endPositionProvider();
	
	/**
	 * @dataProvider getNextProvider
	 */
	public function testGetNext(DataSource $dataSource, $position, $actualValue) {
		$dataSource->moveTo($position);
		
		$this->assertEquals($dataSource->getNext(), $actualValue);
		$this->assertGreaterThan($position, $dataSource->getPosition(), 'Внутрений указатель должен сместится вперед');
	}
	
	abstract public function getNextProvider();
	
	/**
	 * @dataProvider getPreviusProvider
	 */
	public function testGetPrevius(DataSource $dataSource, $position, $actualValue) {
		$dataSource->moveTo($position);
		$this->assertEquals($dataSource->getPrevius(), $actualValue);
		$this->assertGreaterThan($dataSource->getPosition(), $position, 'Внутрений указатель должен сместится назад');
	}
	
	abstract public function getPreviusProvider();
	
	/**
	 * @dataProvider moveNegativeProvider
	 * @expectedException BinarySearch\Exceptions\GoingBeyondData
	 */
	public function testMoveNegative(DataSource $dataSource, $position) {
		$dataSource->moveTo($position);
	}
	
	abstract public function moveNegativeProvider();
	
	/**
	 * @dataProvider dataNegativeProvider
	 * @expectedException BinarySearch\Exceptions\GoingBeyondData
	 */
	public function testDataNegative(DataSource $dataSource) {
		$dataSource->getData();
	}
	
	abstract public function dataNegativeProvider();
	
	/**
	 * @dataProvider nextNegativeProvider
	 * @expectedException BinarySearch\Exceptions\GoingBeyondData
	 */
	public function testNextNegative(DataSource $dataSource, $position) {
		$dataSource->moveTo($position);
		$dataSource->getNext();
	}
	
	abstract public function nextNegativeProvider();
	
	/**
	 * @dataProvider previusNegativeProvider
	 * @expectedException BinarySearch\Exceptions\GoingBeyondData
	 */
	public function testPreviusNegative(DataSource $dataSource, $position) {
		$dataSource->moveTo($position);
		$dataSource->getPrevius();
	}
	
	abstract public function previusNegativeProvider();
}

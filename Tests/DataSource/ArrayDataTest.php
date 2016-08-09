<?php
namespace BinarySearch\Tests\DataSource;

use BinarySearch\DataSource;


class ArrayDataTest extends \BinarySearch\Tests\DataSource\DataSourceTest {
	public function countProvider() {
		return [
			[ 
				new DataSource\ArrayData([]), 
				0 
			], [ 
				new DataSource\ArrayData([1,2,3,4]), 
				4 
			],
			[ 
				new DataSource\ArrayData([3,2,0]), 
				3
			]
		];
	}
	
	public function moveDataProvider() {
		$dataSource = new DataSource\ArrayData([2,'a', 0]);
		
		return [
			[ $dataSource, 0,  2 ], 
			[ $dataSource, 2,  0 ], 
			[ $dataSource, 1, 'a']
		];
	}
	
	public function startPositionProvider() {
		$dataSource = new DataSource\ArrayData([2,'a', 0]);
		
		return [
			[ $dataSource, 0, true ],
			[ $dataSource, 2, false],
			[ $dataSource, 1, false]
		];
	}
	
	public function endPositionProvider() {
		$dataSource = new DataSource\ArrayData([2,'a', 0]);
		
		return [
			[ $dataSource, 0, false],
			[ $dataSource, 2, true ],
			[ $dataSource, 1, false]
		];
	}
	
	public function getNextProvider() {
		$dataSource = new DataSource\ArrayData([2,'a', 0]);
		
		return [
			[ $dataSource, 0, 'a'],
			[ $dataSource, 1, 0 ],
		];
	}
	
	public function getPreviusProvider() {
		$dataSource = new DataSource\ArrayData([2,'a', 0]);
		
		return [
			[ $dataSource, 1, 2],
			[ $dataSource, 2, 'a' ],
		];
	}
	
	public function moveNegativeProvider() {
		$dataSource = new DataSource\ArrayData([1,3,5]);
		
		return [
			[$dataSource, -5],
			[$dataSource, -1],
			[$dataSource, 3],
			[$dataSource, 5]
		];
	}
	
	public function dataNegativeProvider() {
		return [
			[ new DataSource\ArrayData([]) ]
		];
	}
	
	public function nextNegativeProvider() {
		$dataSource = new DataSource\ArrayData([1,3,5]);
		
		return [
			[$dataSource, 2]
		];
	}
	
	public function previusNegativeProvider() {
		$dataSource = new DataSource\ArrayData([1,3,5]);
		
		return [
			[$dataSource, 0]
		];
	}
}

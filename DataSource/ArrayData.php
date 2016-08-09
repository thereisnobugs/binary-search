<?php
namespace BinarySearch\DataSource;

use BinarySearch\Interfaces\DataSource;
use BinarySearch\Exceptions\GoingBeyondData;

class ArrayData implements DataSource {
	protected $data = [];
	protected $position = null;
	private $count = null;
	
	public function __construct(array $data) {
		$this->data = $data;
		$this->count = null;
		
		if ( $this->count() > 0 ) {
			$this->position = 0;
		} else {
			$this->position = null;
		}
	}
	
	public function count() {
		if ( is_null($this->count) ) {
			$this->count = count($this->data);
		}
		return $this->count;
	}
	
	public function moveTo($position) {
		if ($position >= $this->count() || $position < 0) {
			throw new GoingBeyondData('Указана позиция за пределами данных');
		}
		
		$this->position = $position;
	}
	
	public function getData() {
		if ( is_null($this->position) ) {
			throw new GoingBeyondData('Указана позиция за пределами данных');
		}
		
		return $this->data[$this->position];
	}
	
	public function getNext() {
		if ( $this->isEndPosition() ) {
			throw new GoingBeyondData('Достигнут конец данных');
		}
		
		$this->moveTo($this->position + 1);
		return $this->getData();
	}
	
	public function getPrevius() {
		if ( $this->isStartPosition() ) {
			throw new GoingBeyondData('Достигнуто начало данных');
		}
		
		$this->moveTo($this->position - 1);
		return $this->getData();
		
	}
	
	public function getPosition() {
		return $this->position;
	}
	
	public function isStartPosition() {
		return $this->position == 0;
	}
	
	public function isEndPosition() {
		return $this->position + 1 == $this->count();
	}
}

<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Copyright 2011 Spadefoot
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This class represents a result set.
 *
 * @package Leap
 * @category Connection
 * @version 2011-12-13
 *
 * @abstract
 */
abstract class Base_DB_ResultSet extends Kohana_Object implements ArrayAccess, Countable, Iterator, SeekableIterator {

	/**
	 * This variable stores the records.
	 *
	 * @access protected
	 * @var array
	 */
	protected $records;

	/**
	 * This variable stores the current position in the records array.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $position;

	/**
	 * This variable stores the length of the records array.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $size;

	/**
	 * This function initializes the class by wrapping the result set so that all database
	 * result sets are accessible alike.
	 *
	 * @access public
	 * @param array $records					    an array of records
	 * @param integer $size						    the total number of records
	 */
	public function __construct(Array $records, $size) {
		$this->records = $records;
		$this->position = 0;
		$this->size = $size;
	}

	/**
	 * This function returns the total number of records contained in result set.
	 *
	 * @access public
	 * @return integer                              the total number of records
	 */
	public function count() {
		return $this->size;
	}

	/**
	 * This function returns the current record.
	 *
	 * @access public
	 * @return mixed						        the current record
	 */
	public function current() {
		return $this->records[$this->position];
	}

	/**
	 * This function returns a record of the desired object type.
	 *
	 * @access public
	 * @abstract
	 * @param integer $index                        the record's index
	 * @return mixed                                the record
	 */
	public function fetch($index = -1) {
		settype($index, 'integer');
		if ($index < 0) {
			$index = $this->position;
			$this->position++;
		}

		if (isset($this->records[$index])) {
			return $this->records[$index];
		}

		return FALSE;
	}

	/**
	 * This function returns an array of records of the desired object type.
	 *
	 * @access public
	 * @return array                                an array of records
	 */
	public function fetch_all() {
		return $this->records;
	}

	/**
	 * This function frees all data stored in the result set.
	 *
	 * @access public
	 */
	public function free() {
		$this->records = array();
		$this->position = 0;
		$this->size = 0;
	}

	/**
	 * This function returns the value for the named column from the current record.
	 *
	 *     // Gets the value of "id" from the current record
	 *     $id = $results->get('id');
	 *
	 * @param string $name                          the name of the column
	 * @param mixed $default                        the default value should the column
	 *                                              does not exist
	 * @return mixed                                the value for the named column
	 */
	public function get($name, $default = NULL) {
		$record = $this->current();

		if (is_object($record)) {
			try {
				$value = $record->{$name};
				if ( ! is_null($value)) {
					return $value;
				}
			}
			catch (Exception $ex) {}
		}
		else if (is_array($record) && isset($record[$name])) {
			return $record[$name];
		}

		return $default;
	}

	/**
	 * This function returns whether any records were loaded.
	 *
	 * @access public
	 * @return boolean                              whether any records were loaded
	 */
	public function is_loaded() {
		return ($this->size > 0);
	}

	/**
	 * This function returns the position to the current record.
	 *
	 * @access public
	 * @return integer					            the position of the current record
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * This function moves forward the position to the next record, lazy loading only
	 * when necessary.
	 *
	 * @access public
	 */
	public function next() {
		$this->position++;
	}

	/**
	 * This function determines whether a offset exists.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be evaluated
	 * @return boolean                              whether the requested index exists
	 */
	public function offsetExists($offset) {
		return isset($this->records[$offset]);
	}

	/**
	 * This functions gets value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be fetched
	 * @return mixed                                the value at the specified index
	 */
	public function offsetGet($offset) {
		return isset($this->records[$offset]) ? $this->records[$offset] : NULL;
	}

	/**
	 * This functions sets the specified value at the specified offset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be set
	 * @param mixed $value                          the value to be set
	 * @throws Kohana_UnimplementedMethod_Exception indicates the result cannot be modified
	 */
	public function offsetSet($offset, $value) {
        throw new Kohana_UnimplementedMethod_Exception('Message: Invalid to call member function. Reason: Result set cannot be modified.', array());
	}

	/**
	 * This functions allows for the specified offset to be unset.
	 *
	 * @access public
	 * @param integer $offset                       the offset to be unset
	 * @throws Kohana_UnimplementedMethod_Exception indicates the result cannot be modified
	 */
	public function offsetUnset($offset) {
        throw new Kohana_UnimplementedMethod_Exception('Message: Invalid to call member function. Reason: Result set cannot be modified..', array());
	}

	/**
	 * This function returns the current iterator position.
	 *
	 * @access public
	 * @return integer					            the current iterator position
	 */
	public function position() {
		return $this->position;
	}

	/**
	 * This function rewinds the iterator back to starting position.
	 *
	 * @access public
	 */
	public function rewind() {
		$this->position = 0;
	}

    /**
     * This function sets the position pointer to the seeked position.
     *
     * @access public
     * @param integer $position                     the seeked position
     * @throws Kohana_OutOfBounds_Exception         indicates that the seeked position
     *                                              is out of bounds
     */
    public function seek($position) {
        $this->position = $position;
        if ( ! $this->valid()) {
            throw new Kohana_OutOfBounds_Exception('Message: Invalid array position. Reason: The specified position is out of bounds.', array(':position' => $position, ':count' => $this->size));
        }
    }

	/**
	 * This function checks if the current iterator position is valid.
	 *
	 * @access public
	 * @return boolean					            whether the current iterator position is valid
	 */
	public function valid() {
		return isset($this->records[$this->position]);
	}

}
?>
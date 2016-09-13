<?php
namespace Greenfieldr\Tool\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *  (c) 2016 Marcel Wieser <typo3dev@marcel-wieser.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Recursion Service
 *
 * Can be implemented to observe and handle methods which run recursively or
 * otherwise capable of ending in an infinite loop. Using this service you can
 * gently break execution and report a user-friendly error message.
 *
 * @package Tool
 * @subpackage Service
 */
class RecursionService implements \TYPO3\CMS\Core\SingletonInterface  {

	/**
	 * @var string
	 */
	private $_exceptionMessage = 'Recursion problem occurred';

	/**
	 * @var integer
	 */
	private $_level = 0;

	/**
	 * @var integer
	 */
	private $_maxLevel = 16;

	/**
	 * @var integer
	 */
	private $_maxEncounters = 1;

	/**
	 * @var array
	 */
	private $_encountered = array();

	/**
	 * @var boolean
	 */
	private $_autoReset = FALSE;

	/**
	 * Set the message used to prepend Exceptions
	 * @param string $msg
	 * @api
	 */
	public function setExceptionMessage($msg) {
		$this->_exceptionMessage = $msg;
	}

	/**
	 * Get the message used to prepend Exceptions
	 * @return string
	 * @api
	 */
	public function getExceptionMessage() {
		return $this->_exceptionMessage;
	}

	/**
	 * Set automatic resetting of encounters and level (TRUE/FALSE)
	 * @param boolean $reset
	 * @api
	 */
	public function setAutoReset($reset) {
		$this->_autoReset = $reset;
	}

	/**
	 * Set the maximum allowed number of times a particular identifier may be encountered before an Exception is thrown
	 * @param integer $max
	 * @api
	 */
	public function setMaxEncounters($max) {
		$this->_maxEncounters = $max;
	}

	/**
	 * Get the maximum allowed number of encounters
	 * @return integer
	 * @api
	 */
	public function getMaxEncounters() {
		return $this->_maxEncounters;
	}

	/**
	 * Set the maximum allowed recursion level
	 * @param integer $level
	 * @api
	 */
	public function setMaxLevel($level) {
		$this->_maxLevel = $level;
	}

	/**
	 * Get the maximum allowed recursion level
	 * @return integer
	 * @api
	 */
	public function getMaxLevel() {
		return $this->_maxLevel;
	}

	/**
	 * Get the current recursion level
	 * @return integer
	 * @api
	 */
	public function getLevel() {
		return $this->_level;
	}

	/**
	 * Get the identifier last encountered
	 * @return mixed
	 * @api
	 */
	public function getLastEncounter() {
		return array_pop($this->_encountered);
	}

	/**
	 * Increase recursion level (start of implementer function)
	 * @api
	 */
	public function in() {
		$this->_level++;
	}

	/**
	 * Decrease recursion level (end of implementer function)
	 * @api
	 */
	public function out() {
		$this->_level--;
	}

	/**
	 * Encounter $data (usually a string), call this when new values are read in your recursive function
	 * @param mixed $data
	 * @api
	 */
	public function encounter($data) {
		array_push($this->_encountered, $data);
		$this->check();
	}

	/**
	 * Check the current recursion level and encounter status. Call in each iteration of your function
	 * @param string $exitMsg
	 * @return boolean
	 * @throws \Exception
	 * @api
	 */
	public function check($exitMsg='<no message>') {
		$level = $this->getLevel();
		$maxEnc = $this->getMaxEncounters();
		$message = $this->getExceptionMessage();
		if ($this->failsOnLevel()) {
			$msg = $message . ' at level '. $level . ' with message: ' . $exitMsg;
			throw new \Exception($msg);
		}
		if ($this->failsOnMaxEncounters()) {
			$msg = $message . ' at encounter ' . $maxEnc . ' of ' . $maxEnc . ' allowed with message: ' . $exitMsg;
			$this->throwException($msg);
		}
		return TRUE;
	}

	/**
	 * Reset all counters
	 * @api
	 */
	public function reset() {
		$this->_level = 0;
		$this->_encountered = array();
	}

	/**
	 * Throw an Exception - wrapper; check for auto-reset and reset if needed
	 * @param string $message
	 * @throws \Exception
	 */
	private function throwException($message) {
		if ($this->_autoReset === TRUE) {
			$this->reset();
		}
		throw new \Exception($message);
	}

	/**
	 * Check if the current iteration violates level restraints
	 * @return boolean
	 */
	private function failsOnLevel() {
		$level = $this->getLevel();
		$max = $this->getMaxLevel();
		return (bool) ($level >= $max);
	}

	/**
	 * Check if the current iteration violates encounter restraints
	 * @return boolean
	 */
	private function failsOnMaxEncounters() {
		$lastEncounter = $this->getLastEncounter();
		$occurrences = $this->countEncounters($lastEncounter);
		$max = $this->getMaxEncounters();
		return (bool) ($occurrences > $max);
	}

	/**
	 * Count number of times the identifier $encounter has been encountered
	 * @param mixed $encounter
	 * @return int
	 */
	private function countEncounters($encounter) {
		$num = 0;
		foreach ($this->_encountered as $encountered) {
			if ($encountered === $encounter) {
				$num++;
			}
		}
		return (int) $num;
	}

}

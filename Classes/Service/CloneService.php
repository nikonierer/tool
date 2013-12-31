<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 * Copies a DomainObject with treatment of relationship properties according to
 * source code annotations - @copy ignore|clone|reference. Returns a completely
 * fresh DomainObject with either copies of or references to the original
 * related values.
 *
 * @author Claus Due
 * @package Tool
 * @subpackage Service
 */
class Tx_Tool_Service_CloneService implements t3lib_Singleton {

	/**
	 * RecursionHandler instance
	 * @var Tx_Tool_Service_RecursionService
	 */
	public $recursionService;

	/**
	 * ReflectionService instance
	 * @var Tx_Extbase_Reflection_Service $service
	 */
	protected $reflectionService;

	/**
	 * ObjectManager instance
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Inject a RecursionService instance
	 * @param Tx_Tool_Service_RecursionService $recursionService
	 */
	public function injectRecursionService(Tx_Tool_Service_RecursionService $recursionService) {
		$this->recursionService = $recursionService;
	}

	/**
	 * Inject a Reflection Service instance
	 * @param Tx_Extbase_Reflection_Service $service
	 */
	public function injectReflectionService(Tx_Extbase_Reflection_Service $service) {
		$this->reflectionService = $service;
	}

	/**
	 * Inject a Reflection Service instance
	 * @param Tx_Extbase_Object_ObjectManagerInterface $manager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $manager) {
		$this->objectManager = $manager;
	}

	/**
	 * Copy a singe object based on field annotations about how to copy the object
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $object The object to be copied
	 * @return Tx_Extbase_DomainObject_DomainObjectInterface $copy
	 * @api
	 */
	public function copy($object) {
		$className = get_class($object);
		$this->recursionService->in();
		$this->recursionService->check($className);
		$copy = $this->objectManager->get($className);
		$properties = $this->reflectionService->getClassPropertyNames($className);
		foreach ($properties as $propertyName) {
			$tags = $this->reflectionService->getPropertyTagsValues($className, $propertyName);
			$getter = 'get' . ucfirst($propertyName);
			$setter = 'set' . ucfirst($propertyName);
			$copyMethod = $tags['copy'][0];
			$copiedValue = NULL;
			if ($copyMethod !== NULL && $copyMethod !== 'ignore') {
				$originalValue = $object->$getter();
				if ($copyMethod == 'reference') {
					$copiedValue = $this->copyAsReference($originalValue);
				} elseif ($copyMethod == 'clone') {
					$copiedValue = $this->copyAsClone($originalValue);
				}
				if ($copiedValue != NULL) {
					$copy->$setter($copiedValue);
				}
			}
		}
		$this->recursionService->out();
		return $copy;
	}

	/**
	 * Copies Domain Object as reference
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $value
	 * @return Tx_Extbase_DomainObject_DomainObjectInterface
	 */
	protected function copyAsReference($value) {
		if ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
			// objectstorage; copy storage and attach items to this new storage
			// if 1:n mapping is used, items are detached from their old storage - this is
			// a limitation of this type of reference
			$newStorage = $this->objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
			foreach ($value as $item) {
				$newStorage->attach($item);
			}
			return $newStorage;
		} elseif ($value instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
			// 1:1 mapping as reference; return object itself
			return $value;
		} elseif (is_object($value)) {
			// fallback case for class copying - value objects and such
			return $value;
		} else {
			// this case is very unlikely: means someone wished to copy hard type as a reference - so return a copy instead
			return $value;
		}
	}

	/**
	 * Copies Domain Object as clone
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $value
	 * @return Tx_Extbase_DomainObject_DomainObjectInterface
	 * @api
	 */
	protected function copyAsClone($value) {
		if ($value instanceof Tx_Extbase_Persistence_ObjectStorage) {
			// objectstorage; copy storage and copy items, return new storage
			$newStorage = $this->objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
			foreach ($value as $item) {
				$newItem = $this->copy($item);
				$newStorage->attach($newItem);
			}
			return $newStorage;
		} elseif ($value instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
			// DomainObject; copy and return
			/** @var $value Tx_Extbase_DomainObject_DomainObjectInterface */
			return $this->copy($value);
		} elseif (is_object($value)) {
			// fallback case for class copying - value objects and such
			return clone $value;
		} else {
			// value is probably a string
			return $value;
		}
	}

}

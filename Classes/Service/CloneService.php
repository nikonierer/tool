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
 * Copies a DomainObject with treatment of relationship properties according to
 * source code annotations - @copy ignore|clone|reference. Returns a completely
 * fresh DomainObject with either copies of or references to the original
 * related values.
 *
 * @package Tool
 * @subpackage Service
 */
class CloneService implements \TYPO3\CMS\Core\SingletonInterface  {

	/**
	 * RecursionHandler instance
	 * @var \Greenfieldr\Tool\Service\RecursionService
     * @inject
	 */
	public $recursionService;

	/**
	 * ReflectionService instance
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService $service
     * @inject
	 */
	protected $reflectionService;

	/**
	 * ObjectManager instance
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
	 */
	protected $objectManager;

	/**
	 * Copy a singe object based on field annotations about how to copy the object
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object The object to be copied
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $copy
	 * @api
	 */
	public function copy($object) {
		$className = get_class($object);
		$this->recursionService->in();
		$this->recursionService->check($className);

        /** @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $copy */
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
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $value
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	protected function copyAsReference($value) {
		if ($value instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
			// objectstorage; copy storage and attach items to this new storage
			// if 1:n mapping is used, items are detached from their old storage - this is
			// a limitation of this type of reference
			$newStorage = $this->objectManager->get('Tx_Extbase_Persistence_ObjectStorage');
			foreach ($value as $item) {
				$newStorage->attach($item);
			}
			return $newStorage;
		} elseif ($value instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
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
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $value
	 * @return \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 * @api
	 */
	protected function copyAsClone($value) {
		if ($value instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) {
			// objectstorage; copy storage and copy items, return new storage
			$newStorage = $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class);
			foreach ($value as $item) {
				$newItem = $this->copy($item);
				$newStorage->attach($newItem);
			}
			return $newStorage;
		} elseif ($value instanceof \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface) {
			// DomainObject; copy and return
			/** @var $value \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface */
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

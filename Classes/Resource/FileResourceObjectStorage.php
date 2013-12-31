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
 * Supports all usual access methods - which means you can iterate through this
 * in Fluid and access things like {fileFromStorage.filename} and metadata - see
 * Tx_Tool_Resource_FileResource and others. Supports "serialization" to a TYPO3-compatible
 * CSV format based on $basePath for true BE support.
 *
 * @author Claus Due
 * @package Tool
 * @subpackage Resource
 */
class Tx_Tool_Resource_FileResourceObjectStorage extends SplObjectStorage {

	/**
	 * @var Tx_Tool_Service_DomainService
	 */
	protected $domainService;

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * SITE-RELATIVE base path to prefix to all files' filenames in this collection
	 * @var string
	 */
	protected $basePath;

	/**
	 * Type of the object contained - such as Tx_Tool_Resource_FileResource et al.
	 * Has a default value of the most basic File object type as fallback.
	 * @var string
	 */
	protected $objectType = 'Tx_Tool_Resource_FileResource';

	/**
	 * The name of the property on a DomainObject to which this instance belongs.
	 * Necessary to resolve file uploads.
	 * @var string
	 */
	protected $associatedPropertyName;

	/**
	 * The associated DomainObject which has the property containing this object instance
	 * @var Tx_Extbase_DomainObject_AbstractDomainObject
	 */
	protected $associatedDomainObject;

	/**
	 * @param string $csv
	 */
	public function initializeFromCommaSeparatedValues($csv) {
		$files = explode(',', trim(',', $csv));
		foreach ($files as $file) {
			$fileObject = $this->objectManager->get($this->objectType, $this->basePath . $file);
			$this->attach($fileObject);
		}
	}

	/**
	 * @param string $basePath
	 */
	public function setBasePath($basePath) {
		if (substr($basePath, 0, 1) === '/') {
			throw new Exception('FileResourceObjectStorage does not support absolute paths!', 1311692821);
		}
		if (substr($basePath, -1) !== '/') {
			$basePath .= '/';
		}
		$this->basePath = $basePath;
	}

	/**
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}

	/**
	 * @param mixed $objectType
	 */
	public function setObjectType($objectType) {
		if (is_object($objectType)) {
			$this->objectType = get_class($objectType);
		} else {
			$this->objectType = $objectType;
		}
	}

	/**
	 * @return string
	 */
	public function getObjectType() {
		return $this->objectType;
	}

	/**
	 * Define the source parent of this FileResourceObjectStorage - enables automatic
	 * detection of $objectType and $basePath based on $propertyName and TCA.
	 *
	 * @param Tx_Extbase_DomainObject_AbstractDomainObject $associatedDomainObject
	 * @param string $propertyName Name of the property in which this object is contained
	 */
	public function setAssociatedDomainObject(Tx_Extbase_DomainObject_AbstractDomainObject $associatedDomainObject, $propertyName) {
		$annotationValues = $this->domainService->getAnnotationValuesByProperty($associatedDomainObject, $propertyName, 'file');
		// use collected data to set necessary precursor variables
		$this->objectType = array_pop($annotationValues);
		$this->associatedDomainObject = $associatedDomainObject;
		$this->associatedPropertyName = $propertyName;
		$this->basePath = $this->domainService->getUploadFolder($associatedDomainObject, $propertyName);
	}

	/**
	 * @return Tx_Extbase_DomainObject_AbstractDomainObject
	 */
	public function getAssociatedDomainObject() {
		return $this->associatedDomainObject;
	}

	/**
	 * @param string $associatedPropertyName
	 */
	public function setAssociatedPropertyName($associatedPropertyName) {
		$this->associatedPropertyName = $associatedPropertyName;
	}

	/**
	 * CONSTRUCTOR. Allows setting a CSV (after setting ALL necessary precursors)
	 * to initialize a complete FileObjectStorage.
	 *
	 * Requires all values from one of these value sets to function properly:
	 *
	 * $basePath (optional, but must be set beforehand if necessary)
	 * $associatedPropertyName OR $objectType (to identify the object type)
	 * $objectType (if different from default Tx_Tool_Resource_FileResource)
	 *
	 * -- OR --
	 *
	 * $associatedDomainObject, which fills $basePath with TCA uploadFolder
	 * $associatedPropertyName, which allows reflection to get exact $objectType
	 *
	 * @param string $possibleCsv
	 * @param string $possibleAssociatedPropertyName
	 */
	public function __construct($possibleCsv = NULL, $possibleAssociatedPropertyName = NULL) {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->domainService = $this->objectManager->get('Tx_Tool_Service_DomainService');
		$this->fileService = $this->objectManager->get('Tx_Tool_Service_FileService');
		if (is_string($possibleCsv)) {
			$this->attachFromCsv($possibleCsv);
		}
		if (is_string($possibleAssociatedPropertyName)) {
			$this->associatedPropertyName = $possibleAssociatedPropertyName;
		}
	}

	/**
	 * Sets storage objects by CSV relative to basePath
	 *
	 * @param string $csv
	 */
	public function attachFromCsv($csv) {
		foreach (explode(',', $csv) as $filename) {
			$object = $this->objectManager->get($this->objectType, $this->basePath . $filename);
			$this->attach($object);
		}
	}

	/**
	 * Converts the objectstorage to a TYPO3-compatible CSV value. Takes into
	 * account that $basePath may be set, in which case filenames without paths
	 * are concatenated to support TYPO3 DB "file" fields and upload folder.
	 * Files which are selected from within fileadmin for example will be
	 * concatenated with paths, making this compatible with both upload folder and
	 * direct file selection fields in the TYPO3 BE.
	 *
	 * @return string
	 */
	public function __toString() {
		$filenames = array();
		foreach ($this as $fileObject) {
			if ($this->basePath) {
				$filename = $fileObject->getBasename();
			} else {
				$filename = $fileObject->getRelativePath();
			}
			array_push($filenames, $filename);
		}
		return implode(',', $filenames);
	}

}

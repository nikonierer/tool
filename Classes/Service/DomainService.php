<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * DomainService. Reads various meta-information about DomainObject
 * classes, such as related controller names, properties tagged by special
 * annotations, datatypes of properties (without reading data from an instance),
 * getting repository instances, determining plugin names and more.
 *
 *
 * @author Claus Due, Wildside A/S
 * @package Tool
 * @subpackage Service
 */
class Tx_Tool_Service_DomainService implements t3lib_Singleton {

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
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Persistence_Mapper_DataMapFactory
	 */
	protected $dataMapFactory;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

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
	 * @param Tx_Extbase_Object_ObjectManager $manager
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $manager) {
		$this->objectManager = $manager;
	}

	/**
	 * @param Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory
	 */
	public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory) {
		$this->dataMapFactory = $dataMapFactory;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * Get an array of properties of $object which have been annotated with $annotation
	 * optionally restricting the return values by an additional annotation value
	 *
	 * @param mixed $object The object or classname containing properties
	 * @param string $annotation The name of the annotation to search for, for example 'ExtJS' for annotation @ExtJS (case sensitive)
	 * @param string|boolean $value The value to search for among annotation values. Defaults to TRUE which means the annotation must simply be present
	 * @param boolean $addUid If TRUE, the field "uid" will be force-added to the output regardless of annotation
	 * @return array
	 * @api
	 */
	public function getPropertiesByAnnotation($object, $annotation, $value = TRUE, $addUid = TRUE) {
		$propertyNames = array();
		$className = is_object($object) ? get_class($object) : $object;
		$this->recursionService->in();
		$this->recursionService->check($className);
		$properties = $this->reflectionService->getClassPropertyNames($className);
		foreach ($properties as $propertyName) {
			if ($this->hasAnnotation($className, $propertyName, $annotation, $value)) {
				array_push($propertyNames, $propertyName);
			}
		}
		if ($addUid) {
			array_push($propertyNames, 'uid');
		}
		return $propertyNames;
	}

	/**
	 * Resolve a controller name for $object
	 *
	 * @param mixed $object Instance or classname of Model object
	 * @return string
	 * @api
	 */
	public function getControllerName($object) {
		$className = is_object($object) ? get_class($object) : $object;
		return array_pop(explode('_', $className));
	}

	/**
	 * Resolve a backend controller className for $object
	 *
	 * @param mixed $object
	 * @return string
	 * @api
	 */
	public function getBackendControllerClassName($object) {
		$controllerName = $this->getControllerName($object);
		$extensionName = $this->getExtensionName($object);
		$className = 'Tx_' . $extensionName . '_Controller_Backend_' . $controllerName . 'Controller';
		if (class_exists($className)) {
			return $className;
		}
		return NULL;
	}

	/**
	 * Resolve the name of the extension capable of handling this Model Object
	 *
	 * @param mixed $object Instance or classname of Model object
	 * @return string
	 * @api
	 */
	public function getExtensionName($object) {
		$className = is_object($object) ? get_class($object) : $object;
		$parts = explode('_', $className);
		array_shift($parts);
		return array_shift($parts);
	}

	/**
	 * Resolve a plugin name for this Model Object
	 *
	 * @param mixed $object Instance or classname of Model object
	 * @return string
	 * @api
	 */
	public function getPluginName($object) {
		$extensionName = $this->getExtensionName($object);
		$controllerName = $this->getControllerName($object);
		if (class_exists('Tx_Extbase_Service_Extension') === TRUE) {
			$pluginName = $this->objectManager->get('Tx_Extbase_Service_Extension')->getPluginNameByAction($extensionName, $controllerName, 'list');
		} else {
			$pluginName = Tx_Extbase_Utility_Extension::getPluginNameByAction($extensionName, $controllerName, 'list');
		}
		return $pluginName;
	}

	/**
	 * Get the plugin namespace (for URIs) based on Model Object
	 *
	 * @param mixed $object
	 * @return string
	 * @api
	 */
	public function getPluginNamespace($object) {
		$extensionName = $this->getExtensionName($object);
		$pluginName = $this->getPluginName($object);
		if (class_exists('Tx_Extbase_Service_Extension') === TRUE) {
			return $this->objectManager->get('Tx_Extbase_Service_Extension')->getPluginNamespace($extensionName, $pluginName);
		} else {
			return Tx_Extbase_Utility_Extension::getPluginNamespace($extensionName, $pluginName);
		}
	}

	/**
	 * @param mixed $object  Instance or classname
	 * @return string
	 * @api
	 */
	public function getRepositoryClassname($object) {
		$className = is_object($object) ? get_class($object) : $object;
		return str_replace('_Domain_Model_', '_Domain_Repository_', $className) . 'Repository';
	}

	/**
	 * Get an instance of a proper Repository for $object (instance or classname)
	 *
	 * @param mixed $object
	 * @return Tx_Extbase_Persistence_Repository
	 * @api
	 */
	public function getRepositoryInstance($object) {
		$class = $this->getRepositoryClassname($object);
		return $this->objectManager->get($class);
	}

	/**
	 * Gets the absolute path to partial templates for $object
	 *
	 * @return string
	 * @api
	 */
	public function getPartialTemplatePath($object) {
		$controllerName = $this->getControllerName($object);
		$viewConfig = $this->getViewConfiguration($object);
		if ($viewConfig) {
			return $viewConfig['partialRootPath'] . $controllerName . '/';
		} else {
			$resourcePath = $this->getResourcePath($object);
			return $resourcePath . 'Private/Partials/' . $controllerName . '/';
		}
	}

	/**
	 * Returns the View configuration for $object as defined in Typoscript
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $object
	 * @return array
	 * @api
	 */
	public function getViewConfiguration($object) {
		$config = $this->getExtensionTyposcriptConfiguration($object);
		return $config['view'];
	}

	/**
	 * Returns the absolute path to the Resources folder for $object
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $object
	 * @return string
	 * @api
	 */
	public function getResourcePath($object) {
		$extensionName = $this->getExtensionName($object);
		$extensionName = $this->convertCamelCaseToLowerCaseUnderscored($extensionName);
		return t3lib_extMgm::extPath($extensionName, 'Resources/');
	}

	/**
	 * Returns the site-relative path to the Resources folder for $object
	 *
	 * @param Tx_Extbase_DomainObject_DomainObjectInterface $object
	 * @return string
	 * @api
	 */
	public function getResourcePathRel($object) {
		$extensionName = $this->getExtensionName($object);
		$extensionName = $this->convertCamelCaseToLowerCaseUnderscored($extensionName);
		return t3lib_extMgm::siteRelPath($extensionName) . 'Resources/';
	}

	/**
	 * Checks if $className->$propertyName is annotated w/ $annotation having $value
	 *
	 * @param mixed $className The name of the class containing the property. Can be an object instance
	 * @param string $propertyName The name of the property on className to check
	 * @param string $annotation The annotation which must be present
	 * @param string|boolean $value The value which annotation must contain - default is TRUE meaning annotation must simply be present
	 * @return boolean
	 * @api
	 */
	public function hasAnnotation($className, $propertyName, $annotation, $value = TRUE) {
		$className = is_object($className) ? get_class($className) : $className;
		$tags = $this->reflectionService->getPropertyTagsValues($className, $propertyName);
		$annotationValues = $tags[$annotation];
		if ($annotationValues !== NULL && (in_array($value, $annotationValues) || $value === TRUE)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Turns a Tx_Extbase_Persistence_ObjectStorage<ModelObject> into "ModelObject"
	 * @param string $annotation
	 * @return string
	 * @api
	 */
	public function parseObjectStorageAnnotation($annotation) {
		return array_pop(explode('<', trim($annotation, '>')));
	}

	/**
	 * Get data types of supplied properties - if no propertyNames specified gets
	 * all properties as "propertyName" => "dataType"
	 *
	 * @param mixed $object The object or classname containing the properties
	 * @param array $propertyNames Optional list of properties to get - if empty, gets all properties' types
	 * @return array
	 * @api
	 */
	public function getPropertyTypes($object, array $propertyNames = NULL) {
		$className = is_object($object) ? get_class($object) : $object;
		$types = array();
		$properties = $this->reflectionService->getClassPropertyNames($className);
		foreach ($properties as $propertyName) {
			$types[$propertyName] = $this->getPropertyType($object, $propertyName);
		}
		return $types;
	}

	/**
	 * Get the type of a specific property on $object
	 *
	 * @param mixed $object DomainObject or classname of DomainObject
	 * @param string $propertyName
	 * @return string
	 * @api
	 */
	public function getPropertyType($object, $propertyName) {
		$className = is_object($object) ? get_class($object) : $object;
		$tags = $this->reflectionService->getPropertyTagsValues($className, $propertyName);
		return array_shift(explode(' ', $tags['var'][0]));
	}

	/**
	 * Get an array of all $propertyName=>$tags
	 *
	 * @param mixed $object Instance of class from which to read property tags by annotation
	 * @param string $annotation  The annotation which the property must have
	 * @return array
	 * @api
	 */
	public function getAllTagsByAnnotation($object, $annotation) {
		$tagArray = array();
		$className = is_object($object) ? get_class($object) : $object;
		$properties = $this->reflectionService->getClassPropertyNames($className);
		foreach ($properties as $propertyName) {
			if ($this->hasAnnotation($className, $propertyName, $annotation)) {
				$tags = $this->reflectionService->getPropertyTagsValues($className, $propertyName);
				$set = $tags[$annotation];
				$tagArray[$propertyName] = $set;
			}
		}
		return $tagArray;
	}


	/**
	 * Fetches the TS config array from the current extension
	 * @param mixed $object
	 * @return array
	 * @api
	 */
	public function getExtensionTyposcriptConfiguration($object) {
		$setup = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$extensionName = $this->getExtensionName($object);
		$extensionName = strtolower($extensionName);
		if (is_array($setup['plugin.']['tx_' . $extensionName . '.'])) {
			$extensionConfiguration = Tx_Tool_Utility_ArrayUtility::convertTypoScriptArrayToPlainArray($setup['plugin.']['tx_' . $extensionName . '.']);
		} else {
			$extensionConfiguration = NULL;
		}
		return $extensionConfiguration;
	}

	/**
	 * Returns an array of property names and values by searching the $object
	 * for annotations based on $annotation and $value. If $annotation is provided
	 * but $value is not, All properties which simply have the annotation present.
	 * Relational values which have the annotation are parsed through the same
	 * function - sub-elements' properties are exported based on the same
	 * annotation and value
	 *
	 * @param mixed $object The object or classname to read
	 * @param string $annotation The annotation on which to base output
	 * @param string|boolean $value The value to search for; multiple values may be used in the annotation; $value must be present among them. If TRUE, all properties which have the annotation are returned
	 * @param boolean $addUid If TRUE, the UID of the DomainObject will be force-added to the output regardless of annotation
	 * @return array
	 * @api
	 */
	public function getValuesByAnnotation($object, $annotation = 'json', $value = TRUE, $addUid = TRUE) {
		if (is_array($object)) {
			$array = array();
			foreach ($object as $k => $v) {
				$array[$k] = $this->getValuesByAnnotation($v, $annotation, $value, $addUid);
			}
			return $array;
		}
		if (is_object($object)) {
			$className = get_class($object);
		} else {
			$className = $object;
			$object = $this->objectManager->get($className);
		}
		$this->recursionService->in();
		$this->recursionService->check($className);
		$properties = $this->reflectionService->getClassPropertyNames($className);
		$return = array();
		if ($addUid === TRUE) {
			$return['uid'] = $object->getUid();
		}
		foreach ($properties as $propertyName) {
			$getter = 'get' . ucfirst($propertyName);
			if (method_exists($object, $getter) === FALSE) {
				continue;
			}
			if ($this->hasAnnotation($className, $propertyName, $annotation, $value)) {
				$returnValue = $object->$getter();
				if ($returnValue instanceof Tx_Extbase_Persistence_ObjectStorage) {
					$array = $returnValue->toArray();
					foreach ($array as $k=>$v) {
						$array[$k] = $this->getValuesByAnnotation($v, $annotation, $value, $addUid);
					}
					$returnValue = $array;
				} elseif ($returnValue instanceof Tx_Extbase_DomainObject_DomainObjectInterface) {
					$returnValue = $this->getValuesByAnnotation($returnValue, $annotation, $value, $addUid);
				} elseif ($returnValue instanceof DateTime) {
					$returnValue = $returnValue->format('r');
				}
				$return[$propertyName] = $returnValue;
			}
		}
		$this->recursionService->out();
		return $return;
	}

	/**
	 * Returns an array of annotations for $propertyName on $object
	 *
	 * @param mixed $object
	 * @param string $propertyName
	 * @return array
	 * @api
	 */
	public function getAnnotationsByProperty($object, $propertyName) {
		if (is_object($object)) {
			$className = get_class($object);
		} else {
			$className = $object;
		}
		return $this->reflectionService->getPropertyTagsValues($className, $propertyName);
	}

	/**
	 * Returns the values of a single $annotation of $propertyName on $object
	 *
	 * @param mixed $object
	 * @param string $propertyName
	 * @param string $annotationName
	 * @return array
	 * @api
	 */
	public function getAnnotationValuesByProperty($object, $propertyName, $annotationName) {
		$annotations = $this->getAnnotationsByProperty($object, $propertyName);
		return $annotations[$annotationName];
	}

	/**
	 * @param mixed $object
	 * @return string
	 * @api
	 */
	public function getDatabaseTable($object) {
		if (is_object($object)) {
			$className = get_class($object);
		} else {
			$className = $object;
		}
		$map = $this->dataMapFactory->buildDataMap($className);
		return $map->getTableName();
	}

	/**
	 * @param string $table
	 * @return string,
	 * @api
	 */
	public function getObjectType($table) {
		$typoscript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$configuration = $typoscript['config.']['tx_extbase.']['persistence.']['classes.'];
		if (is_array($configuration)) {
			$configuration = Tx_Tool_Utility_ArrayUtility::convertTypoScriptArrayToPlainArray($configuration);
			foreach ($configuration as $objectType=>$definition) {
				if ($definition['tableName'] === $table) {
					return $objectType;
				}
			}
		}
		return NULL;
	}

	/**
	 * @param mixed $subject
	 * @return mixed
	 * @api
	 */
	public function convertLowerCaseUnderscoredToLowerCamelCase($subject) {
		if (is_array($subject)) {
			foreach ($subject as $k => $value) {
				$subject[$k] = $this->convertLowerCaseUnderscoredToLowerCamelCase($value);
			}
		} else {
			$subject = t3lib_div::underscoredToLowerCamelCase($subject);
			$subject{0} = strtolower($subject{0});
		}
		return $subject;
	}

	/**
	 * @param mixed $subject
	 * @return mixed
	 * @api
	 */
	public function convertCamelCaseToLowerCaseUnderscored($subject) {
		if (is_array($subject)) {
			foreach ($subject as $k => $value) {
				$subject[$k] = $this->convertCamelCaseToLowerCaseUnderscored($value);
			}
		} else {
			$subject = t3lib_div::camelCaseToLowerCaseUnderscored($subject);
		}
		return $subject;
	}

	/**
	 * Gets the TCA-defined uploadfolder for $object. If no propertyName, then
	 * all all properties with uploadfolders are returned as keys along with
	 * their respective upload folders as value
	 * @param mixed $object
	 * @param string $propertyName
	 * @return mixed
	 * @api
	 */
	public function getUploadFolder($object, $propertyName = NULL) {
		if (is_object($object) === FALSE) {
			$className = $object;
			$object = $this->objectManager->get($className);
		}
		if ($propertyName === NULL) {
			$properties = Tx_Extbase_Reflection_ObjectAccess::getGettablePropertyNames($object);
			$folders = array();
			foreach ($properties as $propertyName) {
				$uploadFolder = $this->getUploadFolder($object, $propertyName);
				if (is_dir(PATH_site . $uploadFolder)) {
					$folders[$propertyName] = $uploadFolder;
				}
			}
			return $folders;
		}
		global $TCA;
		$tableName = $this->getDatabaseTable($object);
		t3lib_div::loadTCA($tableName);
		$underscoredPropertyName = $this->convertCamelCaseToLowerCaseUnderscored($propertyName);
		$uploadFolder = $TCA[$tableName]['columns'][$underscoredPropertyName]['config']['uploadfolder'];
		return $uploadFolder;
	}

}

<?php
$classPath = t3lib_extMgm::extPath('tool', 'Classes/');
$prefix = 'tx_tool_';
return array(
	$prefix . 'resource_fileresource'					=> $classPath . 'Resource/FileResource.php',
	$prefix . 'resource_fileresourceobjectstorage'		=> $classPath . 'Resource/FileResourceObjectStorage.php',
	$prefix . 'service_authservice'						=> $classPath . 'Service/AuthService.php',
	$prefix . 'service_cloneservice'					=> $classPath . 'Service/CloneService.php',
	$prefix . 'service_fileservice'						=> $classPath . 'Service/DomainService.php',
	$prefix . 'service_jsonservice'						=> $classPath . 'Service/JsonService.php',
	$prefix . 'service_marshallservice'					=> $classPath . 'Service/MarshallService.php',
	$prefix . 'service_recursionservice'				=> $classPath . 'Service/RecursionService.php',
	$prefix . 'service_userservice'						=> $classPath . 'Service/UserService.php',
	$prefix . 'utility_arrayutility'					=> $classPath . 'Utility/ArrayUtility.php',
	$prefix . 'utility_pathutility'						=> $classPath . 'Utility/PathUtility.php',
	$prefix . 'utility_versionutility'					=> $classPath . 'Utility/VersionUtility.php',
);

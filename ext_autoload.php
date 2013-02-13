<?php
$classPath = t3lib_extMgm::extPath('tool', 'Classes/');
$prefixA = 'tx_tool_';
$prefixB = 'Tx_Tool_';
return array(
	$prefixA . 'resource_fileresource'				=> $prefixB . 'Resource_FileResource',
	$prefixA . 'resource_fileresourceobjectstorage'	=> $prefixB . 'Resource_FileResourceObjectStorage',
	$prefixA . 'service_authservice'				=> $prefixB . 'Service_AuthService',
	$prefixA . 'service_cloneservice'				=> $prefixB . 'Service_CloneService',
	$prefixA . 'service_fileservice'				=> $prefixB . 'Service_DomainService',
	$prefixA . 'service_jsonservice'				=> $prefixB . 'Service_JsonService',
	$prefixA . 'service_marshallservice'			=> $prefixB . 'Service_MarshallService',
	$prefixA . 'service_recursionservice'			=> $prefixB . 'Service_RecursionService',
	$prefixA . 'service_userservice'				=> $prefixB . 'Service_UserService',
	$prefixA . 'utility_arrayutility'				=> $prefixB . 'Utility_ArrayUtility',
	$prefixA . 'utility_pathutility'				=> $prefixB . 'Utility_PathUtility',
	$prefixA . 'utility_versionutility'				=> $prefixB . 'Utility_VersionUtility',
);

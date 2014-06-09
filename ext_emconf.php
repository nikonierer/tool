<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tool".
 *
 * Auto generated 04-12-2013 19:36
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Tool: Services and Utilities for Extbase extensions',
	'description' => 'A collection of Services (for injection) and Utilities (for static calling) for Extbase extensions.',
	'category' => 'misc',
	'author' => 'Claus Due',
	'author_email' => 'claus@namelesscoder.net',
	'author_company' => 'Wildside A/S',
	'shy' => '',
	'dependencies' => 'cms,extbase',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '1.1.2',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-6.1.99',
			'cms' => '',
			'extbase' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:18:{s:16:"ext_autoload.php";s:4:"e8df";s:12:"ext_icon.gif";s:4:"68b4";s:9:"README.md";s:4:"4f73";s:33:"Classes/Resource/FileResource.php";s:4:"4519";s:46:"Classes/Resource/FileResourceObjectStorage.php";s:4:"cf9c";s:31:"Classes/Service/AuthService.php";s:4:"d9b1";s:32:"Classes/Service/CloneService.php";s:4:"f809";s:33:"Classes/Service/DomainService.php";s:4:"5870";s:31:"Classes/Service/FileService.php";s:4:"4f16";s:31:"Classes/Service/JsonService.php";s:4:"0190";s:35:"Classes/Service/MarshallService.php";s:4:"b091";s:36:"Classes/Service/RecursionService.php";s:4:"5605";s:31:"Classes/Service/UserService.php";s:4:"66a2";s:32:"Classes/Utility/ArrayUtility.php";s:4:"3202";s:31:"Classes/Utility/PathUtility.php";s:4:"05cf";s:34:"Classes/Utility/VersionUtility.php";s:4:"b79b";s:33:"Documentation/ComplexityChart.png";s:4:"c574";s:30:"Documentation/PyramidChart.png";s:4:"1775";}',
);

?>

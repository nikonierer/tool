<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@namelesscoder.net>
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
 * Authentication service
 *
 * Responds with either TRUE or FALSE depending on various authentication checks
 * such as group membership, logged in BE/FE user etc.
 *
 * @author Claus Due
 * @package Tool
 * @subpackage Service
 */
class Tx_Tool_Service_AuthService implements t3lib_Singleton {

	/**
	 * @var Tx_Tool_Service_UserService
	 */
	protected $userService;

	/**
	 * @param Tx_Tool_Service_UserService $userService
	 */
	public function injectUserService(Tx_Tool_Service_UserService $userService) {
		$this->userService = $userService;
	}

	/**
	 * Returns TRUE only if a FrontendUser is currently logged in. Use argument
	 * to return TRUE only if the FrontendUser logged in must be that specific user.
	 *
	 * @param Tx_Extbase_Domain_Model_FrontendUser $frontendUser
	 * @return boolean
	 * @api
	 */
	public function assertFrontendUserLoggedIn(Tx_Extbase_Domain_Model_FrontendUser $frontendUser = NULL) {
		$currentFrontendUser = $this->userService->getCurrentFrontendUser();
		if (!$currentFrontendUser) {
			return FALSE;
		}
		if ($frontendUser && $currentFrontendUser) {
			if ($currentFrontendUser->getUid() === $frontendUser->getUid()) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return is_object($currentFrontendUser);
	}

	/**
	 * Returns TRUE if a FrontendUserGroup (specific given argument, else not) is logged in
	 * @param mixed $groups One Tx_Extbase_Domain_Model_FrontendUsergroup or ObjectStorage containing same
	 * @return boolean
	 * @api
	 */
	public function assertFrontendUserGroupLoggedIn($groups = NULL) {
		$currentFrontendUser = $this->userService->getCurrentFrontendUser();
		if (!$currentFrontendUser) {
			return FALSE;
		}
		$currentFrontendUserGroups = $currentFrontendUser->getUsergroup();
		if ($groups) {
			if ($groups instanceof Tx_Extbase_Domain_Model_FrontendUserGroup) {
				return $currentFrontendUserGroups->contains($groups);
			} elseif ($groups instanceof Tx_Extbase_Persistence_ObjectStorage) {
				$currentFrontendUserGroupsClone = clone $currentFrontendUserGroups;
				$currentFrontendUserGroupsClone->removeAll($groups);
				return ($currentFrontendUserGroups->count() !== $currentFrontendUserGroupsClone->count());
			}
		}
		return ($currentFrontendUserGroups->count() > 0);
	}

	/**
	 * Returns TRUE only if a backend user is currently logged in. If used,
	 * argument specifies that the logged in user must be that specific user
	 * @param integer $backendUser UID of a specific BE user which is required to be logged in
	 * @return boolean
	 * @api
	 */
	public function assertBackendUserLoggedIn($backendUser=NULL) {
		$currentBackendUser = $this->userService->getCurrentBackendUser();
		if ($backendUser) {
			if ($currentBackendUser['uid'] === $backendUser) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return is_array($currentBackendUser);
	}

	/**
	 * Returns TRUE only if a backend user is logged in and either has any group
	 * (if param left out) or is a member of the group $groups or a group in
	 * the array/CSV $groups
	 * @param mixed $groups Array of group uids or CSV of group uids or one group uid
	 * @return boolean
	 * @api
	 */
	public function assertBackendUserGroupLoggedIn($groups = NULL) {
		if (!$this->assertBackendUserLoggedIn()) {
			return FALSE;
		}
		$currentBackendUser = $this->userService->getCurrentBackendUser();
		$userGroups = explode(',', $currentBackendUser['usergroup']);
		if (count($userGroups) === 0) {
			return FALSE;
		}
		if (is_string($groups)) {
			$groups = explode(',', $groups);
		}
		if (count($groups) > 0) {
			return (count(array_intersect($userGroups, $groups)) > 0);
		}
		return FALSE;
	}

	/**
	 * Returns TRUE only if there is a current user logged in and this user
	 * is an admin class backend user
	 * @return boolean
	 * @api
	 */
	public function assertAdminLoggedIn() {
		if (!$this->assertBackendUserLoggedIn()) {
			return FALSE;
		}
		$currentBackendUser = $this->userService->getCurrentBackendUser();
		return (boolean) $currentBackendUser['admin'];
	}

}

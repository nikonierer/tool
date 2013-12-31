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
 * User Service
 *
 * Gets Frontend or Backend users currently logged in. Uses FrontendUserRepository
 * for fetching Frontend Users.
 *
 * @author Claus Due
 * @package Tool
 * @subpackage Service
 */
class Tx_Tool_Service_UserService implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * @param Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 */
	public function injectFrontendUserRepository(Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
		$query = $this->frontendUserRepository->createQuery();
		$querySettings = $query->getQuerySettings();
		$querySettings->setRespectStoragePage(FALSE);
		$querySettings->setRespectSysLanguage(FALSE);
		$this->frontendUserRepository->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Gets the currently logged in Frontend User
	 *
	 * @return Tx_Extbase_Domain_Model_FrontendUser
	 * @api
	 */
	public function getCurrentFrontendUser() {
		return $this->frontendUserRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
	}

	/**
	 * Returns a be_user record as lowerCamelCase indexed array if a BE user is
	 * currently logged in.
	 *
	 * @return array
	 * @api
	 */
	public function getCurrentBackendUser() {
		return $GLOBALS['BE_USER']->user;
	}

}

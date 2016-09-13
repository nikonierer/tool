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
 * User Service
 *
 * Gets Frontend or Backend users currently logged in. Uses FrontendUserRepository
 * for fetching Frontend Users.
 *
 * @package Tool
 * @subpackage Service
 */
class UserService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * @param \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $frontendUserRepository
	 */
	public function injectFrontendUserRepository(\TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository $frontendUserRepository) {
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
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 * @api
	 */
	public function getCurrentFrontendUser() {
	    /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $user */
	    $user = $this->frontendUserRepository->findByUid((int)$GLOBALS['TSFE']->fe_user->user['uid']);

		return $user;
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

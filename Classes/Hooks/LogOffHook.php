<?php
namespace MV\SocialAuth\Hooks;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 VANCLOOSTER Mickael <vanclooster.mickael@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

class LogOffHook
{

    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @param array $params
     * @param \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication $pObj
     */
    public function postProcessing($params, $pObj)
    {
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        /** @var \MV\SocialAuth\Utility\AuthUtility $authUtility */
        $authUtility = $this->objectManager->get('MV\\SocialAuth\\Utility\\AuthUtility');
        $authUtility->logout();
        session_start();
        session_destroy();
        $pObj->removeCookie('PHPSESSID');
    }
}

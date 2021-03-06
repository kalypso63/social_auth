<?php
namespace MV\SocialAuth\Controller;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

/**
 * AuthController
 */
class AuthController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected $extConfig = array();

    /**
     * Initialize action
     * @return void
     * @throws \Exception
     */
    public function initializeAction()
    {
        $this->extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('social_auth');
        if (!$this->extConfig['users']['storagePid'] || !$this->extConfig['users']['defaultGroup']) {
            throw new \Exception('You must provide a pid for storage user and a default usergroup on Extension manager', 1473863197);
        }
    }

    /**
     * List action
     * @return void
     */
    public function listAction()
    {
        $providers = array();
        foreach ($this->extConfig['providers'] as $key => $parameters) {
            if ($parameters['enabled'] == 1) {
                array_push($providers, rtrim($key, '.'));
            }
        }
        $this->view->assign('providers', $providers);
    }

    /**
     * Connect action
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function connectAction()
    {
        if (!$this->request->getArgument('provider')) {
            throw new \Exception('Provider is required', 1325691094);
        }
        $redirectionUri = null;
        //redirect if login
        if ($GLOBALS['TSFE']->loginUser && is_array($GLOBALS['TSFE']->fe_user->user)) {
            $redirectionUri = $this->request->getArgument('redirect');
            //sanitize url with logintype=logout
            $redirectionUri = preg_replace('/(&?logintype=logout)/i', '', $redirectionUri);
        }
        if (null === $redirectionUri) {
            $this->uriBuilder->setTargetPageUid((int) $GLOBALS['TSFE']->id);
            $redirectionUri = $this->uriBuilder->build();
        }
        $this->redirectToUri($redirectionUri);
    }

    /**
     * Endpoint action
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function endpointAction()
    {
        //catch error (user cancel, access denied...)
        if (isset($_REQUEST['error']) && !empty($_REQUEST['error'])) {
            $this->uriBuilder->setTargetPageUid((int) $GLOBALS['TSFE']->id);
            $redirectionUri = $this->uriBuilder->build();
            $this->redirectToUri($redirectionUri);
        }

        if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
            try {
                \Hybrid_Endpoint::process();
            } catch (\Hybrid_Exception $e) {
                $this->throwStatus(403);
            }
        }
    }
}

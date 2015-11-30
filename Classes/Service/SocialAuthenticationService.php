<?php
namespace MV\SocialAuth\Service;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Sv\AbstractAuthenticationService;
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


class SocialAuthenticationService extends AbstractAuthenticationService {
    /**
     * The prefix Id
     */
    public $prefixId = 'SocialAuthenticationService';
    /**
     * The script rel path
     */
    public $scriptRelPath = 'Classes/Service/SocialAuthenticationService.php';
    /**
     * The extension key
     */
    public $extKey = 'social_auth';

    /**
     * provider
     */
    protected $provider;

    /**
     * @var array
     */
    protected $extConfig = array();

    /**
     * Login data as passed to initAuth()
     */
    protected $loginData = array();

    /**
     * A reference to the calling object
     *
     * @var AbstractUserAuthentication
     */
    protected $parentObject;

    protected $arrayProvider = array(
        'facebook' => 1,
        'google' => 2,
        'twitter' => 3,
        'linkedin' => 4
    );

    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * authUtility
     *
     * @var \MV\SocialAuth\Utility\AuthUtility
     */
    public $authUtility;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * 100 / 101 Authenticated / Not authenticated -> in each case go on with additonal auth
     */
    const STATUS_AUTHENTICATION_SUCCESS_CONTINUE = 100;
    const STATUS_AUTHENTICATION_FAILURE_CONTINUE = 101;
    /**
     * 200 - authenticated and no more checking needed - useful for IP checking without password
     */
    const STATUS_AUTHENTICATION_SUCCESS_BREAK = 200;
    /**
     * FALSE - this service was the right one to authenticate the user but it failed
     */
    const STATUS_AUTHENTICATION_FAILURE_BREAK = 0;


    /**
     * @return bool
     */
    public function init() {
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->signalSlotDispatcher = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
        $this->extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['social_auth']);
        $request = GeneralUtility::_GP('tx_socialauth_pi1');
        $this->provider = htmlspecialchars($request['provider']);
        return parent::init();
    }

    /**
     * Initializes authentication for this service.
     *
     * @param string $subType: Subtype for authentication (either "getUserFE" or "getUserBE")
     * @param array $loginData: Login data submitted by user and preprocessed by AbstractUserAuthentication
     * @param array $authenticationInformation: Additional TYPO3 information for authentication services (unused here)
     * @param AbstractUserAuthentication $parentObject Calling object
     * @return void
     */
    public function initAuth($subType, array $loginData, array $authenticationInformation, AbstractUserAuthentication &$parentObject) {
        $this->authUtility = $this->objectManager->get('MV\\SocialAuth\\Utility\\AuthUtility');
        // Store login and authetication data
        $this->loginData = $loginData;
        $this->authenticationInformation = $authenticationInformation;
        $this->parentObject = $parentObject;
        parent::initAuth($subType, $loginData, $authenticationInformation, $parentObject);
    }

    /**
     * Find usergroup records
     *
     * @return array User informations
     */
    public function getUser(){
        $user = NULL;
        session_start();
        // then grab the user profile
        if($this->provider && $this->isServiceAvailable()){
            //get user
            $hybridUser = $this->authUtility->authenticate($this->provider);
            if($hybridUser){
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
                    /** @var \TYPO3\CMS\Saltedpasswords\Salt\SaltInterface $saltedpasswordsInstance */
                    $saltedpasswordsInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
                    $password = $saltedpasswordsInstance->getHashedPassword(uniqid());
                } else {
                    $password = md5(uniqid());
                }
                $fields = array(
                    'pid' => (int) $this->extConfig['users.']['storagePid'],
                    'lastlogin' => time(),
                    'crdate' => time(),
                    'tstamp' => time(),
                    'usergroup' => $this->extConfig['users.']['defaultGroup'],
                    'name' => $this->cleanData($hybridUser->displayName),
                    'first_name' => $this->cleanData($hybridUser->firstName),
                    'last_name' => $this->cleanData($hybridUser->lastName),
                    'username' => $this->cleanData($hybridUser->displayName),
                    'password' => $password,
                    'email' => $this->cleanData($hybridUser->email),
                    'telephone' => $this->cleanData($hybridUser->phone),
                    'address' => $this->cleanData($hybridUser->address),
                    'city' => $this->cleanData($hybridUser->city),
                    'zip' => $this->cleanData($hybridUser->zip),
                    'country' => $this->cleanData($hybridUser->country),
                    'tx_socialauth_identifier' => $this->cleanData($hybridUser->identifier),
                    'tx_socialauth_source' => $this->arrayProvider[$this->provider]
                );
                //grab image
                if(!empty($hybridUser->photoURL)){
                    $path = PATH_site . 'uploads/pics/';
                    $uniqueName = strtolower($this->provider .'_' .$hybridUser->identifier) . '.jpg';
                    $file = file_get_contents($hybridUser->photoURL);
                    file_put_contents($path . $uniqueName, $file);
                    $fields['image'] = $uniqueName;
                }

                //signal slot to add other fields
                $this->signalSlotDispatcher->dispatch(__CLASS__, 'beforeCreateOrUpdateUser', array($hybridUser, &$fields, $this));

                //if the user exists in the TYPO3 database
                $exist = $this->userExist($hybridUser->identifier);
                if($exist){
                    $new = FALSE;
                    $GLOBALS['TYPO3_DB']->exec_UPDATEquery('fe_users', 'uid='.$exist[0]['uid'], $fields);
                    $userUid = $exist[0]['uid'];
                }else{
                    $new = TRUE;
                    $GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users', $fields);
                    $userUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
                }
                $user = $this->getUserInfos($userUid);
                $user['new'] = $new;
                $user['fromHybrid'] = TRUE;
                if (isset($user['username'])) {
                    $this->login['uname'] = $user['username'];
                }
                $this->signalSlotDispatcher->dispatch(__CLASS__, 'getUser', array($hybridUser, &$user, $this));
            }
        }
        return $user;
    }

    /**
     * Authenticate user
     * @param $user array record
     * @return int One of these values: 100 = Pass, 0 = Failed, 200 = Success
     */
    public function authUser(&$user){
        if (!$user['fromHybrid']) {
            return self::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
        }
        $result = self::STATUS_AUTHENTICATION_FAILURE_CONTINUE;
        if ($user)
            $result = self::STATUS_AUTHENTICATION_SUCCESS_BREAK;
        //signal slot authUser
        $this->signalSlotDispatcher->dispatch(__CLASS__, 'authUser', array($user, &$result, $this));
        return $result;
    }


    /**
     * Returns TRUE if single sign on for the given provider is enabled in ext_conf and is available
     *
     * @return boolean
     */
    protected function isServiceAvailable() {
        return (boolean) $this->extConfig['providers.'][strtolower($this->provider) . '.']['enabled'];
    }


    /**
     * @param $identifier
     * @return mixed
     */
    protected function userExist($identifier){
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid', 'fe_users', '1=1 AND deleted=0 AND tx_socialauth_identifier LIKE "'.$GLOBALS['TYPO3_DB']->quoteStr($identifier, 'fe_users').'"', '','',1);
    }

    /**
     * get user
     * @param $uid integer
     * @return user array
     */
    protected function getUserInfos($uid){
        $where = 'uid = '.intval($uid).' AND deleted=0';
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'fe_users', $where);
    }

    /**
     * Clean Data
     *
     * @param string $str
     * @return string
     */
    protected function cleanData($str) {
        $str = strip_tags($str);
        //Remove extra spaces
        $str = preg_replace('/\s{2,}/', ' ', $str);
        //delete space end & begin
        $str = trim($str);
        if (FALSE === mb_check_encoding($str, 'UTF-8'))
            $str = utf8_encode($str);
        return $str;
    }
}
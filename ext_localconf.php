<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
$boot = function ($_EXTKEY) {
    $composerAutoloadFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Librairies/autoload.php';
    if (file_exists($composerAutoloadFile)) {
        require_once($composerAutoloadFile);
    }
    if (TYPO3_MODE === 'FE') {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'MV.' . $_EXTKEY,
            'Pi1',
            array(
                'Auth' => 'list, connect, endpoint',
            ),
            // non-cacheable actions
            array(
                'Auth' => 'connect, endpoint'
            )
        );
        $extConfig = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('social_auth');
        if ($extConfig['providers']['facebook']['enabled'] || $extConfig['providers']['google']['enabled'] || $extConfig['providers']['twitter']['enabled'] || $extConfig['providers']['instagram']['enabled']) {
            $GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][$_EXTKEY] = 'MV\SocialAuth\Hooks\LogOffHook->postProcessing';
        }
        //add marker to felogin if is loaded
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('felogin')) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['postProcContent'][$_EXTKEY] = 'MV\SocialAuth\Hooks\FeLoginHook->postProcContent';
        }
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY,
        'auth' /* sv type */,
        MV\SocialAuth\Service\SocialAuthenticationService::class /* sv key */,
        array(
            'title' => 'Social Authentification Service',
            'description' => 'authentication for users from social providers (facebook, twitter...)',
            'subtype' => 'authUserFE,getUserFE',
            'available' => true,
            'priority' => 82, /* will be called before default typo3 authentication service */
            'quality' => 82,
            'os' => '',
            'exec' => '',
            'className' => MV\SocialAuth\Service\SocialAuthenticationService::class,
        )
    );

    #Exclude some params
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_socialauth_pi1[provider]';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_socialauth_pi1[redirect]';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_socialauth_pi1[error]';
    //globals namespace for viewhelper
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['socialauth'] = ['MV\\SocialAuth\\ViewHelpers'];
};

$boot($_EXTKEY);
unset($boot);


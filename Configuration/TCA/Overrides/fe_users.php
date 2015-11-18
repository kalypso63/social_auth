<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}


/**
 * Add extra fields to the fe_users
 */
$tca = array(
    'tx_socialauth_source' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:social_auth/Resources/Private/Language/locallang_db.xlf:fe_users.tx_socialauth_source',
        'config' => array(
            'type' => 'select',
            'items' => array(
                array('None', 0),
                array('Facebook', 1),
                array('Google', 2),
                array('Twitter', 3)
            ),
            'size' => 1,
            'maxitems' => 1,
        ),
    ),
    'tx_socialauth_identifier' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:social_auth/Resources/Private/Language/locallang_db.xlf:fe_users.tx_socialauth_identifier',
        'config' => array(
            'type' => 'input',
            'size' => '10',
            'readOnly' => 1,
            'default'=> 0,
        ),
    ),
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tca);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:social_auth/Resources/Private/Language/locallang_db.xlf:fe_users.tab.social, tx_socialauth_source, tx_socialauth_identifier');


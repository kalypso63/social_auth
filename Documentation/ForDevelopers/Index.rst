.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _for-developers:

For developers
==============

3 slots are available to extend service authentification

- beforeCreateOrUpdateUser
- getUser
- authUser

Example of slot integration (This example add birthdate and extbase_type to general fields with beforeCreateOrUpdateUser signal)

* YOUR_EXTENSION/ext_localconf.php

.. code-block:: xml

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('social_auth')) {
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
        $signalSlotDispatcher->connect(
            'MV\\SocialAuth\\Service\\SocialAuthenticationService',
            'beforeCreateOrUpdateUser',
            'Vendor\\ExtensionKey\\Slots\\UserAuthentificationSlot',
            'beforeCreateOrUpdateUser'
        );
    }

* YOUR_EXTENSION/Classes/Slots/UserAuthentificationSlot.php

.. code-block:: xml

    <?php
    namespace Vendor\ExtensionKey\Slots;
    class UserAuthentificationSlot {
        /**
         * @param $hybridUser string
         * @param $fields array
         * @param \MV\SocialAuth\Service\SocialAuthenticationService $pObj
         */
        public function beforeCreateOrUpdateUser($hybridUser, &$fields = array(), \MV\SocialAuth\Service\SocialAuthenticationService $pObj) {
            $fields['tx_extbase_type'] = 'Tx_YourExtension_User';
            if($hybridUser->birthDay && $hybridUser->birthMonth && $hybridUser->birthYear){
                $fields['birthdate'] = mktime(NULL, NULL, NULL, (int) $hybridUser->birthMonth, (int) $hybridUser->birthDay, (int) $hybridUser->birthYear);
            }
        }
    }

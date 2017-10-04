# social_auth
Social Auth service for TYPO3 with Hybrid Auth API (Facebook, Twitter, Google + & LinkedIn)

## Installation instructions

* Install the extension using the Extension Manager
* Register new app on Facebook, Twitter, Google or LinkedIn and follow the instructions. Some examples are detailed on Hybrid auth user guide [http://hybridauth.sourceforge.net/userguide.html]
* Configure via Extension Manager and add key + appId for each social provider & set options for fe_users creation (users pid and default usergroup id are required !)
* Storage User Pid should be different from classic fe_users PID to prevent unique username 
* New for 8.7 instance, set file storage uid and path for fe_users.image (FAL is used now for image field)
* Add the static TS (typoscript) to your typoscript template

## Frontend plugin integration

Two ways exist to integrate social auth on FE

* Add Social auth plugin on your page. It create a link for each enabled providers
* Create links on your fluid template like this (add namespace viewhelper at first):

`{namespace socialauth=MV\SocialAuth\ViewHelpers}`

`<f:link.page pageType="1316773681" additionalParams="{tx_socialauth_pi1:{provider:'facebook',redirect:'{socialauth:currentUri()}'}}" noCacheHash="TRUE">Facebook</f:link.page>`

You can change redirect URL with a custom page (like profil page) using {f:uri.page()} viewhelper instead of {socialauth:currentUri()}

## Bootstrap Social CSS

Bootstrap social css is used by default on every views. You can disable it via Constant editor

## Integration with Felogin

If felogin is used, you can add marker ###SOCIAL_AUTH### to your custom felogin template. Typoscript for Felogin is loaded on main TS

To custom render of generated links. Modify Typoscript like this :

```
plugin.tx_felogin_pi1{
    socialauth.wrap = <ul>|</ul>
    socialauth_provider{
        facebook = TEXT
        facebook{
            typolink{
                #Custom class or title
                #ATagParams =
            }
            wrap = <li>|</li>
            stdWrap.dataWrap = {LLL:EXT:social_auth/Resources/Private/Language/locallang.xlf:facebook.label}
        }
        twitter < .facebook
        twitter{
            stdWrap.dataWrap = {LLL:EXT:social_auth/Resources/Private/Language/locallang.xlf:twitter.label}
        }
        google < .facebook
        google{
            stdWrap.dataWrap = {LLL:EXT:social_auth/Resources/Private/Language/locallang.xlf:google.label}
        }
    }
}
```

## Signal Slots for developer

3 slots are available to extend service authentification

* beforeCreateOrUpdateUser
* getUser
* authUser

###Example of slot integration (This example add birthdate and extbase_type to general fields with beforeCreateOrUpdateUser signal)

YOUR_EXTENSION/ext_localconf.php

```
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('social_auth')) {
    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
    $signalSlotDispatcher->connect(
        'MV\\SocialAuth\\Service\\SocialAuthenticationService',
        'beforeCreateOrUpdateUser',
        'Vendor\\ExtensionKey\\Slots\\UserAuthentificationSlot',
        'beforeCreateOrUpdateUser'
    );
}
```

YOUR_EXTENSION/Classes/Slots/UserAuthentificationSlot.php

```
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
```

## Troubles after upgrade with 1.1.0 version

If we have an error like "Could not scan for classes", just uninstall extension and clear cache before upgrade it.

## Google + configuration

Don't forget to activate Google+ API on [Google developer console] and fill the field Authorized redirect uri with http://yourdomain/?type=1316773682&hauth.done=Google

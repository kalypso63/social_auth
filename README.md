# social_auth
Social Auth service for TYPO3 with Hybrid Auth API (Facebook, Twitter & Google +)

## Installation instructions

* Install the extension using the Extension Manager
* Configure via Extension Manager and add key + appId for each social provider & set options for fe_users creation
* Add the static TS (typoscript) to your typoscript template

## Frontend plugin integration

Two ways exist to integrate social auth on FE

* Add Social auth plugin on your page. It create a link for each enabled providers
* Create links on your fluid template like this :

`<f:link.page pageType="1316773681" additionalParams="{tx_socialauth_pi1:{provider:'facebook'}}" noCacheHash="TRUE">Facebook</f:link.page>`


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

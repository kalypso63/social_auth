<?php
namespace MV\SocialAuth\Hooks;

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

class FeLoginHook
{

    /**
     * @param array $params
     * @param $pObj
     */
    public function postProcContent($params, $pObj)
    {
        $markerArray['###SOCIAL_AUTH###'] = '';
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['social_auth']);
        $providers = array();
        foreach ($extConfig['providers.'] as $key => $parameters) {
            if ($parameters['enabled'] == 1) {
                array_push($providers, rtrim($key, '.'));
            }
        }
        if (is_array($providers) && count($providers) > 0) {
            rsort($providers);
            foreach ($providers as $provider) {
                $providerConf = $pObj->conf['socialauth_provider.'][$provider.'.'];
                $customTypolink = array(
                    'parameter' => $GLOBALS['TSFE']->id,
                    'additionalParams' => '&type=1316773681&tx_socialauth_pi1[provider]='.$provider.'&tx_socialauth_pi1[redirect]='.\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
                    'useCashHash' => false
                );
                $providerConf['typolink.'] = ($providerConf['typolink.']) ? array_merge($providerConf['typolink.'], $customTypolink) : $customTypolink;
                $markerArray['###SOCIAL_AUTH###'] = $pObj->cObj->stdWrap($markerArray['###SOCIAL_AUTH###'], $providerConf);
            }
            //wrap all
            $markerArray['###SOCIAL_AUTH###'] = $pObj->cObj->stdWrap($markerArray['###SOCIAL_AUTH###'], $pObj->conf['socialauth.']);
        }
        return $pObj->cObj->substituteMarkerArrayCached($params['content'], $markerArray);
    }
}

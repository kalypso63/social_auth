<?php

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('social_auth');

$result = array(
    'Hybrid_Auth' => $extensionPath . 'Resources/Public/Php/HybridAuth/hybridauth/Hybrid/Auth.php',
    'Hybrid_Endpoint' => $extensionPath . 'Resources/Public/Php/HybridAuth/hybridauth/Hybrid/Endpoint.php',
    'Hybrid_Provider_Adapter' => $extensionPath . 'Resources/Public/Php/HybridAuth/hybridauth/Hybrid/Provider_Adapter.php',
    'Hybrid_User_Profile' => $extensionPath . 'Resources/Public/Php/HybridAuth/hybridauth/Hybrid/User_Profile.php',
);

return $result;

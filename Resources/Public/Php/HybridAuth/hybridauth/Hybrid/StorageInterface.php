<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */

/**
 * HybridAuth storage manager interface
 */
interface Hybrid_Storage_Interface
{

    public function config($key, $value = null);

    public function get($key);

    public function set($key, $value);

    public function clear();

    public function delete($key);

    public function deleteMatch($key);

    public function getSessionData();

    public function restoreSessionData($sessiondata = null);
}

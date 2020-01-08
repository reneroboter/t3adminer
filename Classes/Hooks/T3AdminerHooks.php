<?php
namespace jigal\t3adminer\Hooks;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2018 Jigal van Hemert <jigal.van.hemert@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

class T3AdminerHooks
{

    /**
     * Hook to remove t3adminer session on logoff
     *
     * @param $parameters
     * @param AbstractUserAuthentication $parentObject
     * @return void
     */
    public function logoffHook(&$parameters, AbstractUserAuthentication $parentObject) {
        if (isset($_SESSION)) {  // if there is already a session running
            session_write_close(); // save and close it
        }
        if ($sessionId = $_COOKIE['tx_t3adminer']) { // if tx_t3adminer session cookie exist
            session_id($sessionId);  // select tx_t3adminer session
            session_start(); // start tx_t3adminer session
            unset(
                $_SESSION['pwds'],
                $_SESSION['ADM_driver'],
                $_SESSION['ADM_user'],
                $_SESSION['ADM_password'],
                $_SESSION['ADM_server'],
                $_SESSION['ADM_db'],
                $_SESSION['ADM_extConf'],
                $_SESSION['ADM_hideOtherDBs'],
                $_SESSION['ADM_SignonURL'],
                $_SESSION['ADM_LogoutURL'],
                $_SESSION['ADM_uploadDir']
            );
            session_write_close(); // close tx_t3adminer session
            $parentObject->removeCookie('tx_t3adminer'); // remove tx_t3adminer session cookie
        }
    }
}

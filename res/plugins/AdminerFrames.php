<?php

/** Allow using Adminer inside a frame (disables ClickJacking protection)
 *
 * @author Jakub Vrana, http://www.vrana.cz/
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerFrames
{
    public $sameOrigin;

    /**
     * @param bool $sameOrigin Allow running from the same origin only
     */
    public function __construct($sameOrigin = false)
    {
        $this->sameOrigin = $sameOrigin;
    }

    public function headers()
    {
        if ($this->sameOrigin) {
            header('X-Frame-Options: SameOrigin');
        }
        header('X-XSS-Protection: 0');

        return false;
    }

}
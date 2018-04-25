<?php

/** This plugin replaces UNIX timestamps with human-readable dates in your local format.
 * Mouse click on the date field reveals timestamp back.
 *
 * @link https://www.adminer.org/plugins/#use
 * @author Anonymous
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class AdminerReadableDates
{

    protected $prepend;

    function __construct()
    {
        $this->prepend = '<script' . nonce() . '>';
        $this->prepend .= <<<EOT
document.addEventListener('DOMContentLoaded', function(event) {
	var date = new Date();
	var tds = document.querySelectorAll('span[class="datetimefield"]');
	for (var i = 0; i < tds.length; i++) {
		var text = tds[i].innerHTML.trim();
		if (text !== '0') {
			date.setTime(parseInt(text) * 1000);
			tds[i].oldDate = text;

			// tds[i].newDate = date.toUTCString().substr(5); // UTC format
			tds[i].newDate = date.toLocaleString();	// Local format
			// tds[i].newDate = date.toLocaleFormat('%e %b %Y %H:%M:%S'); // Custom format - works in Firefox only

			tds[i].newDate = '<span style="color: #009900">' + tds[i].newDate + '</span>';
			tds[i].innerHTML = tds[i].newDate;
			tds[i].dateIsNew = true;

			tds[i].addEventListener('click', function(event) {
				this.innerHTML = (this.dateIsNew ? this.oldDate : this.newDate);
				this.dateIsNew = !this.dateIsNew;
			});
		}
	}
});
</script>
EOT;
    }

    public function head()
    {
        echo $this->prepend;
    }

    public function selectVal($val, $link, $field, $original)
    {
        $return = (
            $val === null
            ? "<i>NULL</i>" :
            (
                preg_match("~char|binary~", $field["type"])
                    && !preg_match("~var~", $field["type"])
                ? "<code>$val</code>"
                : $val
            )
        );
        if (preg_match('~blob|bytea|raw|file~', $field["type"]) && !self::is_utf8($val)) {
            $return = "<i>" . sprintf('%d byte(s)', strlen($original)) . "</i>";
        }
        if (preg_match('~json~', $field["type"])) {
            $return = "<code class='jush-js'>$return</code>";
        }
        $return = ($link ? "<a href='" . h($link) . "'" . (self::is_url($link) ? " rel='noreferrer'" : "") . ">$return</a>" : $return);

        $table = $GLOBALS['a'];
        if (in_array($field['field'], ['tstamp', 'crdate'])) {
            $return = '<span class="datetimefield">' . $return . '</span>';
        } elseif (!empty($_SESSION['ADM_tca'][$table]['columns'][$field['field']])) {
            $tcaConfiguration = $_SESSION['ADM_tca'][$table]['columns'][$field['field']]['config'];
            if ($tcaConfiguration['type'] === 'input' && !empty($tcaConfiguration['eval'])) {
                $evalOptions = explode(',', $tcaConfiguration['eval']);
                array_walk($evalOptions, 'trim');
                if (in_array('date', $evalOptions, true) || in_array('datetime', $evalOptions, true)) {
                    $return = '<span class="datetimefield">' . $return . '</span>';
                }
            }
        }

        return $return;
    }

    /** Check whether the column looks like boolean
     * @param array single field returned from fields()
     * @return bool
     */
    protected static function like_bool($field) {
        return preg_match("~bool|(tinyint|bit)\\(1\\)~", $field["full_type"]);
    }

    /** Escape for HTML
     * @param string
     * @return string
     */
    protected static function h($string) {
        return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
    }

    /** Check whether the string is in UTF-8
     * @param string
     * @return bool
     */
    protected static function is_utf8($val) {
        // don't print control chars except \t\r\n
        return (preg_match('~~u', $val) && !preg_match('~[\\0-\\x8\\xB\\xC\\xE-\\x1F]~', $val));
    }

    /** Check whether the string is URL address
     * @param string
     * @return string "http", "https" or ""
     */
    protected static function is_url($string) {
        $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component //! IDN
        return (preg_match("~^(https?)://($domain?\\.)+$domain(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $string, $match) ? strtolower($match[1]) : ""); //! restrict path, query and fragment characters
    }
}

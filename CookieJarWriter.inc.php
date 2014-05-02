<?php

/**
 * cURL's cookie jar file editing tool
 * Note:
 * Each record (line) in a cookie jar file consist of the following 7 tab-separated fields:
 * domain, tailmatch, path, secure, expires, name and value
 * In this class the first 4 fields are shared to all records,
 * and the rest 3 fields are individual to each record
 */

class CookieJarWriter
{
    /**
     * @var string - cookie jar file name (path)
     */
    protected $sFile = false;

    /**
     * @var array - array of the shared cookie field values
     */
    protected $aPrefix = array(
        '',      // domain
        'FALSE', // tailmatch
        '/',     // path
        'FALSE', // secure
    );

    /**
     * @var string - aggregate of the shared cookie fields
     */
    protected $sPrefix = '';


    /**
     * @param string $file - file name (path)
     * @param string $domain - value of "domain" field
     */
    function __construct($file, $domain = '') {
        if (!$file)
            return;
        $this->sFile = $file;
        $this->setPrefix($domain);
    }

    /**
     * Initialize/modify shared cookie fields
     * @param string $domain - value of "domain" field
     * @param bool $bTail - value of "tailmatch" field
     * @param string $path - value of "path" field
     * @param bool $bSecure - value of "secure" field
     */
    function setPrefix($domain = null, $bTail = null, $path = null, $bSecure = null) {
        if (!is_null($domain))
            $this->aPrefix[0] = $domain;
        if (!is_null($bTail))
            $this->aPrefix[1] = $bTail ? 'TRUE' : 'FALSE';
        if (!is_null($path))
            $this->aPrefix[2] = $path;
        if (!is_null($bSecure))
            $this->aPrefix[3] = $bSecure ? 'TRUE' : 'FALSE';
        if ($this->aPrefix[0])
            $this->sPrefix = implode("\t", $this->aPrefix) . "\t";
    }

    /**
     * Main method to add/modify/remove a cookie record to/in/from the cookie jar file
     * @param string $name - name of cookie variable (individual field "name")
     * @param string $value - value of cookie variable (individual field "value")
     * @param int $life - life of a cookie record
     * @return string|bool - content of a cookie record
     */
    function setCookie($name, $value = null, $life = 0) {
        if (!$this->sFile || !$this->sPrefix || !$name)
            return false;
        $cont = file_exists($this->sFile) ? file_get_contents($this->sFile) : '';
        $cr = (strpos($cont, "\r\n") !== false) ? "\r\n" : "\n";
        $a_rows = explode($cr, trim($cont, $cr));
        $i_row = -1;
        foreach ($a_rows as $i => $row) {
            if (strpos($row, $this->sPrefix) === 0 &&
                strpos($row, "\t" . $name . "\t") !== false) {
                $i_row = $i;
                break;
            }
        }
        $ret = true;
        if (!is_null($value)) {
            // add/modify:
            $life = intval($life);
            if ($i_row < 0)
                $i_row = count($a_rows);
            $n_exp = ($life > 0) ? (time() + $life * 24 * 60 * 60) : 1;
            $a_rows[$i_row] = $ret =
                $this->sPrefix . implode("\t", array($n_exp, $name, $value));
        }
        else if ($i_row >= 0) {
            // remove:
            unset($a_rows[$i_row]);
        }
        file_put_contents($this->sFile, implode($cr, $a_rows) . $cr);
        return $ret;
    }

    /**
     * Shortcut method to add/modify a cookie record to/in the cookie jar file
     * @param string $name
     * @param string $value
     * @param int $life
     * @return string|bool
     */
    function addCookie($name, $value, $life = 0) {
        return $this->setCookie($name, $value, $life);
    }

    /**
     * Shortcut method to remove a cookie record from the cookie jar file
     * @param string $name
     * @return bool
     */
    function removeCookie($name) {
        return $this->setCookie($name);
    }
}

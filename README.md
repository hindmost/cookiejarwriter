Cookie Jar Writer
===============

It is an utility PHP class for runtime editing cURL's cookie jar file. In other words, it allows to add custom cookie variables to a cookie jar file within sequence of cURL requests.
Each record (line) in a cookie jar file consists of the following 7 tab-separated fields:
* domain
* tailmatch
* path
* secure
* expires
* name
* value

For more details about cookie file format see [here] (http://www.cookiecentral.com/faq/#3.5).
In this class the first 4 fields are shared to all records, and the rest 3 fields are individual to each record.


Usage Sample
-------------

``` php
function getByCurl($aOpts, $url, $urlRef = '') {
    $hc = curl_init();
    curl_setopt_array($hc, $aOpts);
    curl_setopt($hc, CURLOPT_URL, $url);
    curl_setopt($hc, CURLOPT_REFERER, $urlRef);
    $cont = curl_exec($hc);
    $b_ok = curl_errno($hc) == 0 && curl_getinfo($hc, CURLINFO_HTTP_CODE) == 200;
    curl_close($hc);
    return $b_ok ? $cont : false;
}

$file_cook = dirname(__FILE__). '/cookiejar.txt';
$a_curl_opts = array(
    CURLOPT_NOBODY => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_COOKIEFILE => $file_cook,
    CURLOPT_COOKIEJAR => $file_cook,
);
$url1 = 'http://example.com/page1.html';
$url2 = 'http://example.com/page2.html';

require_once "CookieJarWriter.inc";
// create an instance of CookieJarWriter:
$o_cw = new CookieJarWriter($file_cook, 'example.com');
// some curl request:
$cont = getByCurl($a_curl_opts, $url1);
// some processing on a request's response:
// ...
// add some cookie variable expired in 3 days:
$rec = $o_cw->addCookie('some_variable_name', 'some_variable_value', 3);
// some curl request after cookie jar modification:
$cont = getByCurl($a_curl_opts, $url2, $url1);

```


License
-------------
* [GNU General Public License] (http://opensource.org/licenses/gpl-license)

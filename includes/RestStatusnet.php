<?php

/**
* Inspired by http://gitorious.org/twitter-api-test-suite
*
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    http://www.gnu.org/licenses/gpl-3.0.txt
*/

class RestStatusnet extends RestAbstract
{

    /**
    * Initialize. This method must be called before all others
    * Extended to support Statusnet authentication
    *
    * @param string $url
    * @param string $method 'GET', 'PUT', 'POST', or 'DELETE'
    * @param string|array $data
    */
    public function init($url, $method, $data = null)
    {
        if ('OAUTH' == strtoupper($GLOBALS['CONFIG']['AUTH_TYPE']))
        {
            $consumer = new OAuthConsumer(
                $GLOBALS['CONFIG']['OAUTH_CONSUMER_KEY'],
                $GLOBALS['CONFIG']['OAUTH_CONSUMER_SECRET']
                );

            $token = new OAuthToken(
                $GLOBALS['CONFIG']['OAUTH_TOKEN'],
                $GLOBALS['CONFIG']['OAUTH_TOKEN_SECRET']
                );

            // Hack to detect binary files (prepended with the @ symbol)
            // These must be passed via multipart/form-data
            $curl_file_upload = false;
            if (is_array($data)) {
                foreach ($data as $key => $val) {
                    if (preg_match('/^@/', $val)) {
                        $curl_file_upload = true;
                        break;
                    }
                }
            }

            $req = OAuthRequest::from_consumer_and_token($consumer, $token, $method, $url, ($curl_file_upload ? null : $data));
            $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);

            if ('GET' == strtoupper($method) || $curl_file_upload) {
                $url = $req->to_url();
            }
            elseif ('POST' == strtoupper($method)) {
                $data = $req->to_postdata();
            }
            else {
                // TODO, PUT and DELETE?
                throw new Exception('Unsuported method. Use GET, or POST.');
            }

            parent::init($url, $method, $data);

        }
        elseif ('PASSWORD' == strtoupper($GLOBALS['CONFIG']['AUTH_TYPE']))
        {
            parent::init($url, $method, $data);
            curl_setopt($this->curl, CURLOPT_USERPWD, "{$GLOBALS['CONFIG']['USERNAME']}:{$GLOBALS['CONFIG']['PASSWORD']}");
        }
        else
        {
            throw new Exception('Unsuported AUTH_TYPE in config. Use OAUTH, or PASSWORD.');
        }

    }


}
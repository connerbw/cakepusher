<?php

/**
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    Dual licensed under MIT and GNU LGPL v2.1
*/

class Toolbox {

    // Static class, no cloning or instantiating allowed
    final private function __construct() { }
    final private function __clone() { }

    /**
    * @param float $lat1
    * @param float $lon1
    * @param float $lat2
    * @param float $lon2
    * @param string $unit (optional) 'km', 'miles'
    * @return float
    */
    static function distance($lat1, $lon1, $lat2, $lon2, $unit = 'km') {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        if ('km' == strtolower($unit)) {
            return ($miles * 1.609344);
        }
        else {
            return $miles;
        }
    }


    /**
    * @param float $lat1
    * @param float $lon1
    * @param float $lat2
    * @param float $lon2
    * @return array [ lat3, lon3 ]
    */
    static function midpoint ($lat1, $lon1, $lat2, $lon2) {

        // TODO: This function sucks, improve it
        // @see: http://www.movable-type.co.uk/scripts/latlong.html

        $lat3 = ($lat1 + $lat2) / 2;
        $lon3 = ($lon1 + $lon2) / 2;

        return array($lat3, $lon3);
    }


    /**
    * Parse a string and turn it into an array of tokens
    *
    * @param string $string
    * @return array
    */
    static function tokens($string)
    {
        return preg_split('/([\s\-_,:;?!\/\(\)\[\]{}<>\r\n"]|(?<!\d)\.(?!\d))/', mb_strtolower($string), null, PREG_SPLIT_NO_EMPTY);
    }


    /**
    * Does a string contains a synonym?
    *
    * @param string $string
    * @param array $synonyms
    * @return bool
    */
    static function matches($string, array $synonyms)
    {
        array_walk($synonyms, create_function('&$val', '$val = mb_strtolower($val);'));
        $string = array_intersect(self::tokens($string), $synonyms);

        if (count($string)) {
            return true;
        }
        else {
            return false;
        }
    }


    /**
    * @return array dents to send
    */
    static function hustler()
    {
        $url = $GLOBALS['CONFIG']['STATUSNET_SERVER'] . "/api/statuses/friends_timeline/{$GLOBALS['CONFIG']['STATUSNET_TIMELINE']}.xml";
        $xml = simplexml_load_file($url);

        $updates = array();

        foreach ($GLOBALS['CONFIG']['RECOMMENDATIONS'] as $word => $synonyms)
        {
            // --------------------------------------------------------------------
            // Search each XML node for word/synonyms
            // If found, keep screen name and georss
            // --------------------------------------------------------------------

            $found = array();

            foreach ($xml->status as $node)
            {
                // Skip old status updates
                $timelapse = strtotime('now') - strtotime($node->created_at);
                if ($timelapse > $GLOBALS['CONFIG']['MAX_TIMELAPSE']) {
                    continue;
                }

                // Find matches
                $match = Toolbox::matches($node->text, $synonyms);
                if ($match)
                {
                    $screen_name = (string) $node->user->screen_name;
                    $geo_point = (string) $node->geo->children('georss', true)->point;

                    // Don't find yourself
                    if ($screen_name == $GLOBALS['CONFIG']['STATUSNET_TIMELINE']) {
                        continue;
                    }

                    if (!isset($found[$screen_name])) {
                        // The most recent status, from top to bottom
                        $found[$screen_name] = $geo_point;
                    }
                }
            }

            // Remove matches with no geo point
            foreach ($found as $key => $val) {
                if (!trim($val)) {
                    unset($found[$key]);
                }
            }

            // Debug
            if ($GLOBALS['CONFIG']['DEBUG']) {
                echo "### Matches Found for keyword: $word\n";
                print_r($found);
                echo "\n\n";
            }

            // --------------------------------------------------------------------
            // For each found item, compare to another, concatenate a possible dent
            // --------------------------------------------------------------------

            if (count($found) > 1)
            {
                $i = 0;
                foreach ($found as $screen_name1 => $geo_point1)
                {
                    list($lat1, $lon1) = explode(' ', $geo_point1);
                    $j = 0;
                    foreach($found as $screen_name2 => $geo_point2) {
                        if ($j > $i)
                        {
                            list($lat2, $lon2) = explode(' ', $geo_point2);
                            $distance = Toolbox::distance($lat1, $lon1, $lat2, $lon2);

                            // Debug
                            if ($GLOBALS['CONFIG']['DEBUG']) {
                                echo "### Distance between @$screen_name1 & @$screen_name2:\n";
                                echo "$distance KM \n";
                                echo "\n\n";
                            }

                            if ($distance < $GLOBALS['CONFIG']['MAX_DISTANCE'])
                            {
                                list($lat3, $lon3) = Toolbox::midpoint($lat1, $lon1, $lat2, $lon2);

                                // Debug
                                if ($GLOBALS['CONFIG']['DEBUG']) {
                                    echo "### Midpoint between @$screen_name1 & @$screen_name2:\n";
                                    echo "$lat3, $lon3 \n";
                                    echo "\n\n";
                                }

                                $map_url = Toolbox::buildMapUrlFromYellowApi($word, $lat3, $lon3);

                                // TODO: Improve throtling mechanism
                                sleep(1);

                                // IMPORTANT:
                                // Curl fails if first character is an @ symbol,
                                // Leave a space!
                                $updates[] = " @{$screen_name1} @{$screen_name2} You should hook up for {$word}: {$map_url}";
                            }
                        }
                        ++$j;
                    }
                    ++$i;
                }
            }
        }

        return $updates;
    }


    /**
    * Queries the YellowAPI with Keyword, Latitude, and Longitue
    * Returns a Map URL to closest match
    *
    * @param string $keyword
    * @param float $lat1
    * @param float $lon1
    * @return string map url
    */
    static function buildMapUrlFromYellowApi($keyword, $lat1, $lon1) {

        static $cheap_cache = array();
        $cheap_cache_key = md5($keyword . $lat1 . $lon1);
        if (isset($cheap_cache[$cheap_cache_key])) {
            return $cheap_cache[$cheap_cache_key];
        }

        if ($GLOBALS['CONFIG']['DEBUG']) {
            echo "### Querying Yellow API for [$keyword, $lat1, $lon1]\n";
            echo "\n\n";
        }

        $server_ip = (isset($_SERVER['SERVER_ADDR'])) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';

        $url = $GLOBALS['CONFIG']['YELLOW_API_SERVER'] . "/FindBusiness/?what={$keyword}&where=cZ{$lon1},{$lat1}&fmt=XML&pgLen=5&apikey={$GLOBALS['CONFIG']['YELLOW_API_KEY']}&UID={$server_ip}";

        $xml = simplexml_load_file($url);

        $map_url = null;

        foreach ($xml->Listings->Listing as $node)
        {
            if ($node->Distance < ($GLOBALS['CONFIG']['MAX_DISTANCE'] / 2)) {

                $name = urlencode($node->Name);
                $lat2 = $node->GeoCode->Latitude;
                $lon2 = $node->GeoCode->Longitude;

                $map_url = "https://maps.google.com/maps?q=";
                $map_url .= "{$lat2},+{$lon2}+({$name})&iwloc=A";

                break;
            }
        }

        if ($map_url) $cheap_cache[$cheap_cache_key] = $map_url;

        return $map_url;
    }


    /**
    * Post updates to Status.Net
    *
    * @param array $updates
    */
    static function dent($updates) {

        $url = $GLOBALS['CONFIG']['STATUSNET_SERVER'] . '/api/statuses/update.xml';
        $rest = new RestStatusnet();

        foreach ($updates as $status) {

            $data = array(
                'status' => $status,
                );
            $rest->init($url, 'POST', $data);
            $rest->exec();

            // TODO: Improve throtling mechanism
            sleep(1);
        }

    }


}
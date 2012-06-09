<?php

/**
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    Dual licensed under MIT and GNU LGPL v2.1
*/

require_once(dirname(__FILE__) . '/config.php'); // Configuration
require_once(dirname(__FILE__) . '/initialize.php'); // Initialization

$keywords = '';
foreach ($GLOBALS['CONFIG']['RECOMMENDATIONS'] as $key => $val) {
    foreach ($val as $val2) {
        $keywords .= "$val2, ";
    }
}
$keywords = rtrim($keywords, ', ');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

    <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
    <title>Cake Pusher</title>

    <!--
    Thanks to Nicolas Gallagher for CSS
    http://nicolasgallagher.com/pure-css-speech-bubbles/
    -->
    <link rel='stylesheet' type='text/css' href='media/default.css' />

</head><body><div id="container">

    <h2><img src="media/cake-icon.png" alt="Cake Pusher Icon" /><br />
     I'm your mamma, I'm your daddy, I'm that pastry in the alley...
    </h2>

    <p>
    <strong>Cake Pusher</strong> will periodically scan its own <a href="http://identi.ca/<?php echo $GLOBALS['CONFIG']['IDENTICA_TIMELINE']; ?>/all">Home Timeline</a> for the keywords:
    </p>

    <p><em>
    <?php echo $keywords; ?>
    </em></p>

    <p>
    If it can be determined that two people have talked about cakes within 30 minutes
    and 50km of each other, Cake Pusher will dent:
    </p>

    <p class="triangle-border">@person1 @person2 You should hook up for cake: http://ur1.ca/foo</p>

    <p>
    Where FOO is a map to a café that sells cake somewhere in between the two.
    </p>


    <p>
    Done for <a href="http://hackdays.ca/">#HackMTL</a> on June 9th 2012.<br />
    APIs used: <a href="http://status.net/">Status.Net</a>, <a href="http://yellowapi.com">YellowAPI.com</a><br />
    Code availabe at <a href="https://github.com/connerbw/cakepusher">GitHub</a>.
    </p>


</div></body></html>
<?php
header("Content-Type: text/html; charset=utf-8");

require_once(dirname(__FILE__) . '/' . 'igetui/utils/ApnsUtils.php');

getPushInfoLen();


function getPushInfoLen() {
    $rep = ApnsUtils :: validatePayloadLength("阳春三月天气新，湖中丽人花照春。满船罗绮载花酒，燕歌赵舞留行云。五月湖中采莲女，笑隔荷花共人语",
						"", "b", "a", "", "4", "com.gexin.ios.silence", "DDDD",0);
    var_dump($rep);
    echo ("<br><br>");
}

?>

<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_NOTICE);
# Use the Curl extension to query Google and get back a page of results
$context = stream_context_create(array('http' => array('timeout' => 5)));

$arrPreviousURLs = explode(PHP_EOL, file_get_contents('https://glot.io/snippets/fakv95iyid/raw'));

$i = 0;
do {
	$strCurrentTime = getDateTime($i, 'Y/m/d');
	$url = "http://www.iptvsrc.net/$strCurrentTime.html";
	echo $url . PHP_EOL;
	$i++;
} while (get_http_response_code($url) != "200" && $i < 10);

$arrSixthURLs = getURLData($url, 'article', "\n");

$arrStartArray = getURLData("https://freedailyiptv.com/world-m3u-free-daily-iptv-list-" . date("m-Y") . "/", 'a');
$arrThirdArray = getURLData("https://freedailyiptv.com/stream-database/", 'span');
$arrSecondURLs = getURLData('http://vlctest.eu5.net/', 'span');
$arrFirstURLs = getURLData('https://www.oneplaylist.space/', 'span');

$url = getTodaysURL('https://www.en.m3uiptv.com/iptv-links-free-m3u-playlist');
$arrForthURLs = getURLData($url, 'pre');

$url = getTodaysURL('https://m3uiptv.xyz/free-iptv-links-m3u-playlist');
$arrURLs = getURLData($url, 'pre');

$i = 0;
do {
	$strCurrentTime = getDateTime($i, 'd-F-Y');
	$url = "http://www.iptvurls.com/iptv-m3u-playlist-$strCurrentTime/";
	echo $url . PHP_EOL;
	$i++;
} while (get_http_response_code($url) != "200" && $i < 10);

$arrFifthURLs = getURLData($url, 'p', "\n");

//$arrStaticURLs = array('https://drive.google.com/uc?authuser=0&id=1YVbzmZkqeizCmdfrgdtqizQ6NQIPm2M4&export=download');

$arrURLs = array_unique(array_merge($arrPreviousURLs, $arrSixthURLs, $arrThirdArray, $arrForthURLs, $arrStartArray, $arrFirstURLs, $arrSecondURLs, $arrURLs, $arrFifthURLs));

//$arrURLs = array('http://roseflo.com:25461/get.php?username=bff912bcc9&password=bff912bd00&type=m3u');
$strFinal = '';
$stradtFinal = '';
$strChannelCount = 0;
$arrLastWorkingURLs = array();

foreach ($arrURLs as $index => $strURL) {
	$intFailedCount = 0;
	$intSuccessCount = 0;
	$intTotalChannelCount = 0;
	$intHDChannelCount = 0;
	//show_status($index + 1, count($arrURLs), 30, $strChannelCount);

	if (preg_match('(latinorigin|61.171|190.2.154|185.233.185|82.214.119|217.182.134.18|tvku|trk555|duplexfx|zoneiptv|audio|setmedia|music|77.233.1.12|130.185.250.102|188.35.9.4|udp|stream|mp3|mp4|mkv|217.23.8.25|kosmowka|play|radio|95.86.32.7|184.154.202.243|livewipserver|.online)', strtolower($strURL)) === 1) {
		//unset($arrURLs[$index]);
		continue;
	}

	if (preg_match('(http)', strtolower($strURL)) !== 1) {
		//unset($arrURLs[$index]);
		continue;
	}

	$strURL = (strstr($strURL, " ", true)) ? strstr($strURL, " ", true) : $strURL;
	echo $strURL . PHP_EOL;

	$intHTTPCode = get_http_response_code($strURL);
	if (false == in_array($intHTTPCode, array("200")) || empty($strURL)) {
		//echo get_http_response_code($strURL);
		//unset($arrURLs[$index]);
		continue;
	}

	$strContent = @file_get_contents($strURL);

	/*if ($strChannelCount >= 500) {
		unset($arrURLs[$index]);
		continue;
	}*/

	$arrstrContent = explode('#EXTINF', $strContent);
	unset($strContent);
	foreach ($arrstrContent as $index1 => $value) {

		if ($intFailedCount >= 5) {
			//echo "FAILED : " . $strURL . PHP_EOL;
			break;
		}
		$channelname = substr(strrchr($value, ","), 1);
		if (preg_match('/^(?:hindi:|english:|marathi:|in:|in-|in -|\(in\)|in\||in \||hindi \||hindi\||hindi -|hindi-|english -|english-|english\||english \||marathi\||marathi \||adt|xxx)/', strtolower(trim($channelname))) === 1) {
			$strgroupTitle = $index;
			$intTotalChannelCount++;

			if (preg_match('(tamil|malayalam|spain|bein|latin|telugu|benin|islam|punjabi|vod)', strtolower($value)) !== 1) {
				$intHDChannelCount++;
				$strgroupTitle = 'HD ' . $index;
				$url = trim(explode(PHP_EOL, $value)[1]);

				if ($intSuccessCount < 6) {
					$fp = @fopen($url, "r", false, $context);
				} else {
					$fp = TRUE;
				}

				if (!$fp) {
					//echo "FAiled ==>" . explode(PHP_EOL, $value)[1];
					$intFailedCount++;
				} else {
					$intSuccessCount++;
					if (preg_match('(adt|xxx)', strtolower($value)) === 1) {
						$stradtFinal .= '#EXTINF' . str_replace(array(':-1,', ':0,'), array(':-1,' . ' group-title=\"' . $strgroupTitle . '\", ', ':0,' . ' group-title=\"' . $strgroupTitle . '\", '), addslashes($value)) . PHP_EOL;
					} else {
						$strFinal .= '#EXTINF' . str_replace(array(':-1,', ':0,'), array(':-1,' . ' group-title=\"' . $strgroupTitle . '\", ', ':0,' . ' group-title=\"' . $strgroupTitle . '\", '), addslashes($value)) . PHP_EOL;
						$strChannelCount++;
					}

					if (is_resource($fp)) {
						fclose($fp);
					}

					if (!in_array($strURL, $arrLastWorkingURLs, true)) {
						array_push($arrLastWorkingURLs, $strURL);
					}
				}
			}
		}

		unset($arrstrContent[$index1]);
	}
	echo " Final Total Channels: " . $strChannelCount . PHP_EOL;
	if ($intSuccessCount >= 10) {
		file_put_contents('latest.m3u', $strFinal, FILE_APPEND | LOCK_EX);
	}

	$strFinal = '';
	unset($arrstrContent);
}
/*$myfile = fopen("newfile.m3u", "w") or die("Unable to open file!");
fwrite($myfile, $strFinal);
fclose($myfile);*/
if (file_exists('latest.m3u')) {
	echo "Writing in the paste URL";
	writeFile(file_get_contents('latest.m3u'), 'f9p029xslv', 'Channels');
}

if (false == empty($stradtFinal)) {
	echo "Writing in the paste URL";
	writeFile($stradtFinal, 'f9tkwi5gse', 'ADT');
}

if (false == empty($arrLastWorkingURLs)) {
	writeFile(implode(PHP_EOL, $arrLastWorkingURLs), 'fakv95iyid', 'URLs');
}

@unlink('latest.m3u');
exit;

function getTodaysURL($strURL) {
	$i = 0;
	do {
		$strCurrentTime = getDateTime($i);
		$url = "$strURL-$strCurrentTime/";
		echo $url . PHP_EOL;
		$i++;
	} while (get_http_response_code($url) != "200" && $i < 10);

	return $url;
}

function get_http_response_code($url) {
	stream_context_set_default(array('http' => array('timeout' => 5)));
	$headers = @get_headers($url);
	return substr($headers[0], 9, 3);
}

function getDateTime($intInvertDays, $strFormat = 'd-m-Y') {
	$objDateTime = new DateTime();
	$objDateTime->setTimeZone(new DateTimeZone('IST'));
	$strDateInterval = new DateInterval("P" . $intInvertDays . "D");
	$strDateInterval->invert = 1;
	$objDateTime->add($strDateInterval);
	return $objDateTime->format($strFormat);
}

function getURLData($url, $strTag, $strSeparator = PHP_EOL) {
	$arrURLs = array();
	$arrFinalArray = array();

	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$html = curl_exec($ch);
	curl_close($ch);

	# Create a DOM parser object
	$dom = new DOMDocument();
# Parse the HTML from Google.
	# The @ before the method call suppresses any warnings that
	# loadHTML might throw because of invalid HTML in the page.
	@$dom->loadHTML($html);

# Iterate over all the <a> tags
	foreach ($dom->getElementsByTagName($strTag) as $link) {
		# Show the <a href>
		$arrURLs = array_map('trim', explode($strSeparator, $link->nodeValue));
		$arrURLs = array_filter($arrURLs);
		$arrFinalArray = array_merge($arrFinalArray, $arrURLs);
	}

	return array_filter($arrFinalArray, 'removeInvalidLinks');
}

function removeInvalidLinks($var) {
	// returns whether the value is 'other'
	return (strpos($var, 'http') !== false);
}

function writeFile($strFinal, $strFileName, $strChannel) {
	$strFinal = '#EXTM3U' . PHP_EOL . $strFinal;
// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://snippets.glot.io/snippets/' . $strFileName);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_POSTFIELDSIZE, -1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"language\": \"plaintext\", \"title\": \"$strChannel\", \"public\": false, \"files\": [{\"name\": \"main.txt\", \"content\": \"" . $strFinal . "\"}]}");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	$headers = array();
	$headers[] = 'Authorization: Token a48c4968-8309-4374-8431-f43b92f21ad3';
	$headers[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);

	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
}

function show_status($done, $total, $size = 30, $channelcount) {

	static $start_time;

	// if we go over our bound, just ignore it
	if ($done > $total) {
		return;
	}

	if (empty($start_time)) {
		$start_time = time();
	}

	$now = time();

	$perc = (double) ($done / $total);

	$bar = floor($perc * $size);

	$status_bar = "\r[";
	$status_bar .= str_repeat("=", $bar);
	if ($bar < $size) {
		$status_bar .= ">";
		$status_bar .= str_repeat(" ", $size - $bar);
	} else {
		$status_bar .= "=";
	}

	$disp = number_format($perc * 100, 0);

	$status_bar .= "] $disp%  $done/$total";

	$rate = ($now - $start_time) / $done;
	$left = $total - $done;
	$eta = round($rate * $left, 2);

	$elapsed = $now - $start_time;

	$status_bar .= " Channels: $channelcount";

	$status_bar .= " remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

	echo "$status_bar  ";

	flush();

	// when done, send a newline
	if ($done == $total) {
		echo "\n";
	}

}
?>

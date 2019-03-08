<?php
set_time_limit(0);
# Use the Curl extension to query Google and get back a page of results
$context = stream_context_create(array('http' => array('timeout' => 5)));

$arrStartArray = getURLData("https://freedailyiptv.com/world-m3u-free-daily-iptv-list-" . date("m-Y") . "/", 'a');
$arrThirdArray = getURLData("https://freedailyiptv.com/stream-database/", 'span');
$arrSecondURLs = getURLData('http://vlctest.eu5.net/', 'span');
$arrFirstURLs = getURLData('https://www.oneplaylist.space/', 'span');

$i = 0;
do {
	$strCurrentTime = getDateTime($i);
	$url = "https://www.en.m3uiptv.com/iptv-links-free-m3u-playlist-$strCurrentTime/";
	echo $url . PHP_EOL;
	$i++;
} while (get_http_response_code($url) != "200" && $i < 10);
$arrForthURLs = getURLData($url, 'pre');

$i = 0;
do {
	$strCurrentTime = getDateTime($i);
	$url = "https://m3uiptv.xyz/free-iptv-links-m3u-playlist-$strCurrentTime/";
	echo $url . PHP_EOL;
	$i++;
} while (get_http_response_code($url) != "200" && $i < 10);

$arrURLs = getURLData($url, 'pre');

//$arrStaticURLs = array('https://drive.google.com/uc?authuser=0&id=1YVbzmZkqeizCmdfrgdtqizQ6NQIPm2M4&export=download');

$arrURLs = array_unique(array_merge($arrThirdArray, $arrForthURLs, $arrStartArray, $arrFirstURLs, $arrSecondURLs, $arrURLs));

//$arrURLs = array('https://drive.google.com/a/anyonehome.com/uc?id=1YVbzmZkqeizCmdfrgdtqizQ6NQIPm2M4');
$strFinal = '';
$stradtFinal = '';
$strChannelCount = 0;

foreach ($arrURLs as $index => $strURL) {
	$intFailedCount = 0;
	$intSuccessCount = 0;
	$intTotalChannelCount = 0;
	$intHDChannelCount = 0;
	if (strpos($strURL, 'http') === false || strpos($strURL, '130.185.250.102') !== false || strpos($strURL, 'udp') !== false || strpos($strURL, 'stream') !== false) {
		unset($arrURLs[$index]);
		continue;
	}
	echo $strURL . PHP_EOL;
	$intHTTPCode = get_http_response_code($strURL);
	if (false == in_array($intHTTPCode, array("200")) || empty($strURL)) {
		//echo get_http_response_code($strURL);
		unset($arrURLs[$index]);
		continue;
	}

	$strContent = file_get_contents($strURL);

	/*if ($strChannelCount >= 500) {
		unset($arrURLs[$index]);
		continue;
	}*/

	$arrstrContent = explode('#EXTINF', $strContent);
	unset($strContent);
	foreach ($arrstrContent as $value) {

		/*if ($intFailedCount >= 5) {
			echo "FAILED : " . $strURL . PHP_EOL;
			break;
		}*/

		if (preg_match('(hindi:|english:|marathi:|in:|in-|\(in\)|in\||in \||hindi \||hindi\||english\||english \||marathi\||marathi \|)', strtolower($value)) === 1) {
			$strgroupTitle = $index;
			$intTotalChannelCount++;

			if (strpos(strtolower($value), 'hd') !== false) {
				$intHDChannelCount++;
				$strgroupTitle = 'HD ' . $index;
				$url = trim(explode(PHP_EOL, $value)[1]);

				if ($intSuccessCount < 6) {
					$fp = @fopen($url, "r", false, $context);
				} else {
					$fp = TRUE;
				}

				if (!$fp = @fopen($url, "r", false, $context)) {
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
					fclose($fp);
				}
			}
		}
	}
	unset($arrstrContent);
	//echo "Channels: " . $intTotalChannelCount . " HD Channels: " . $intHDChannelCount . " Final Total Channels: " . $strChannelCount . PHP_EOL;
}
/*$myfile = fopen("newfile.m3u", "w") or die("Unable to open file!");
fwrite($myfile, $strFinal);
fclose($myfile);*/
if (false == empty($strFinal)) {
	echo "Writing in the paste URL";
	writeFile($strFinal, 'f9p029xslv', 'Channels');
}

if (false == empty($stradtFinal)) {
	echo "Writing in the paste URL";
	writeFile($stradtFinal, 'f9tkwi5gse', 'ADT');
}

exit;

function get_http_response_code($url) {
	stream_context_set_default(array('http' => array('timeout' => 5)));
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}

function getDateTime($intInvertDays) {
	$objDateTime = new DateTime();
	$objDateTime->setTimeZone(new DateTimeZone('IST'));
	$strDateInterval = new DateInterval("P" . $intInvertDays . "D");
	$strDateInterval->invert = 1;
	$objDateTime->add($strDateInterval);
	return $objDateTime->format('d-m-Y');
}

function getURLData($url, $strTag) {
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
		$arrURLs = array_map('trim', explode(PHP_EOL, $link->nodeValue));
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
?>

<?php

set_time_limit(0);

$objDateTime = new DateTime();
$objDateTime->setTimeZone(new DateTimeZone('IST'));
$strDateInterval = new DateInterval("P1D");
$strDateInterval->invert = 1;
$objDateTime->add($strDateInterval);
$strCurrentTime = $objDateTime->format('d-m-Y');

# Use the Curl extension to query Google and get back a page of results
$url = "https://www.en.m3uiptv.com/iptv-links-free-m3u-playlist-$strCurrentTime/";

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
foreach ($dom->getElementsByTagName('pre') as $link) {
	# Show the <a href>
	$arrURLs = array_map('trim', explode(PHP_EOL, $link->nodeValue));
	$arrURLs = array_filter($arrURLs);
}
//$arrURLs = array('http://www.sansat.net:25461/get.php?username=bryan&password=bryan123&type=m3u');

$strFinal = '';
foreach ($arrURLs as $index => $strURL) {
	echo $strURL . PHP_EOL;
	if (get_http_response_code($strURL) != "200" || empty($strURL)) {
		continue;
	} else {
		$strContent = file_get_contents($strURL);
	}

	$arrstrContent = explode('#EXTINF', $strContent);
	unset($strContent);
	foreach ($arrstrContent as $value) {
		if (preg_match('(hindi:|english:|marathi:|in:|adt:|in\||in \||hindi \||hindi\||english\||english \||marathi\||marathi \|)', strtolower($value)) === 1) {
			$strgroupTitle = $index;
			if (strpos(strtolower($value), 'HD') !== false) {
			    echo $index . ' - HD';
			}
			
			$strFinal .= '#EXTINF' . $value . 'group-title="' . $strgroupTitle . '"' . PHP_EOL;			
		}
	}
}

/*$myfile = fopen("newfile.m3u", "w") or die("Unable to open file!");
fwrite($myfile, $strFinal);
fclose($myfile);*/

if (false == empty($strFinal)) {
	echo "Writing in the paste URL";
	$strFinal = '#EXTM3U' . PHP_EOL . $strFinal;

// Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, 'https://snippets.glot.io/snippets/f9nt3c5w6h');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_POSTFIELDSIZE, -1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"language\": \"plaintext\", \"title\": \"4k channels\", \"public\": false, \"files\": [{\"name\": \"main.txt\", \"content\": \"" . $strFinal . "\"}]}");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

	$headers = array();
	$headers[] = 'Authorization: Token a48c4968-8309-4374-8431-f43b92f21ad3';
	$headers[] = 'Content-Type: application/json';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	print_r($result);exit;
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);
	exit;
}

function get_http_response_code($url) {
	$headers = get_headers($url);
	return substr($headers[0], 9, 3);
}
?>

<?php
require "PHP-Feeds.php";

// Downloading The Feed
$ch = curl_init();
curl_setopt_array($ch, [
	CURLOPT_URL => "FEED URL HERE",
	CURLOPT_TIMEOUT => 30,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true
]);
$rawfeed = curl_exec($ch);
curl_close($ch);
$rawfeed = str_replace(" & ", " &amp; ", $rawfeed); // Some feeds may have unencoded amps, which can result in errors.

// Parsing the Feed
$feed = Feed::fromString($rawfeed);
unset($rawfeed);
if(!$feed->isTypeSupported())
{
	die($feed->type." is not supported!\n");
}

// Listing the Feed's Articles
echo "The 10 most recent articles of ".$feed->name.":\n";
$i = 0;
foreach($feed->getArticles() as $article)
{
	echo "- ".$article->getName();
	if($i++ == 10)
	{
		break;
	}
}

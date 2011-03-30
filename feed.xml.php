<?php

/**
 * view file for displaying content as feed
 *
 * @package    content
 */
header("Content-Type: application/xml; utf-8");
$rss = new rss();
$feed = $rss->getFeed();
echo $feed;
die;
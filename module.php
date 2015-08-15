<?php

namespace modules\rss;

use diversen\conf;
use diversen\date;
use diversen\html;
use diversen\moduleloader;
use diversen\uri;

/**
 * class for doing feeds
 */
class module {

    public function feedAction() {

        header("Content-Type: application/xml; utf-8");
        $rss = new self();
        $feed = $rss->getFeed();
        echo $feed;
        die;
    }

    public static function getFeedLink($options) {

        $link = '<div class="rss_module">';
        $link.= "<a href=\"http://$_SERVER[HTTP_HOST]";
        $link.= "/rss/feed/1/$options[reference]/feed.xml\" ";
        $link.= "rel= \"nofollow\" target = \"_blank\">\n";
        $link.= "<img src=\"/images/rss.gif\" alt=\"rss\" /></a>";
        $link.= '</div>';
        return $link;
    }

    /**
     *
     * @return  string  feed string
     */
    public function getFeed() {
        $uri = uri::getInstance();
        $module = $uri->fragment(3);
        $extra = $uri->fragment(4);
        
        // get module specific RSS
        moduleloader::includeModule($module);
        $class = "modules\\" . moduleloader::modulePathToClassName($module) . "\\module";
        $rows = $class::getRowsRSS();

        // compose RSS
        $str = '';
        $str.= $this->getStart();
        $str.= $this->getItems($rows);
        $str.= $this->getEnd();
        return $str;
    }

    public static function subModulePostContent($options) {
        if (!isset($mode)) {
            return;
        }
        if ($options['mode'] == 'list') { 
            return self::getFeedLink($options);
        }
    }

    /**
     *
     * @return string   details
     */
    public static function getStart() {
        $details = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
        $details.= "<rss version=\"2.0\">\n";
        $details.= "<channel>\n";
        $details.= "<title>$_SERVER[HTTP_HOST]</title>\n";
        $details.= "<link>http://$_SERVER[HTTP_HOST]</link>\n";
        $details.= "<description>" . conf::$vars['coscms_main']['description'] . "</description>";
        $details.= "<language>" . conf::$vars['coscms_main']['lang'] . "</language>\n";
        return $details;
    }

    public static function getEnd() {
        $end = '';
        $end.= "</channel>\n";
        $end.= "</rss>\n";
        return $end;
    }

    /**
     *
     * @return  string  items in the feed
     */
    public static function getItems($rows) {
        $items = '';
        foreach ($rows as $key => $val) {
            $val = html::specialEncode($val);
            $items.= "<item>\n";
            $items.= "<title>$val[title]</title>\n";
            $items.= "<link>http://$_SERVER[HTTP_HOST]$val[url]</link>\n";
            $items.= "<description>$val[abstract]</description>\n";
            $items.= "<pubDate>" . self::timestampToPubdate($val['created']) . "</pubDate>\n";
            $items.= "</item>";
        }
        return $items;
    }

    public static function timestampToPubdate($ts) {
        return date('D, d M Y H:i:s O', strtotime($ts));
    }
}

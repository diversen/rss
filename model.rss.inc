<?php

/**
 * class for doing feeds
 */
class rss {
    public static function getFeedLink($options){
        $link = "<p>";
        $link.= "<a href=\"http://$_SERVER[HTTP_HOST]";
        $link.= "/rss/feed/1/$options[reference]/feed.xml\" ";
        $link.= "rel= \"nofollow\" target = \"_blank\">\n";
        $link.= "<img src=\"/images/rss.gif\" alt=\"rss\" /></a>";
        $link.= '</p>';
        return $link;
    }

    /**
     *
     * @return  string  feed string
     */
    public function getFeed(){
        $uri = uri::getInstance();
        $module = $uri->fragment(3);
        $extra = $uri->fragment(4);
        
        // Ugly
        if ($extra && $extra != 'feed.xml') $module.="/$extra";
        include_module($module);

        $class = moduleLoader::modulePathToClassName($module);
        $rows = $class::getRowsRSS();

        $str = '';
        $str.= $this->getStart();
        $str.= $this->getItems($rows);
        $str.= $this->getEnd();
        return $str;
    }

    public static function subModulePostContent ($options) {
        return self::getFeedLink($options);
    }

    /**
     *
     * @return string   details
     */
    public static function getStart(){
        $details = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
        $details.= "<rss version=\"2.0\">\n";
        $details.= "<channel>\n";
        $details.= "<title>$_SERVER[HTTP_HOST]</title>\n";
        $details.= "<link>http://$_SERVER[HTTP_HOST]</link>\n";
        $details.= "<description>" . config::$vars['coscms_main']['description'] . "</description>";
        $details.= "<language>" . config::$vars['coscms_main']['lang'] . "</language>\n";
        return $details;
    }

    public static function getEnd (){
        $end = '';
        $end.= "</channel>\n";
        $end.= "</rss>\n";
        return $end;
    }

    /**
     *
     * @return  string  items in the feed
     */
    public static function getItems($rows){
        $items = '';
        foreach ($rows as $key => $val){
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

    public static function timestampToPubdate ($ts){
        return date ('D, d M Y H:i:s O', strtotime ($ts));
    }
}

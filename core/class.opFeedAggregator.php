<?php
defined('_OP') or die('Access denied');
/**
 *  Copyright (C) 2009 Lars Boldt
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class opFeedAggregator {
    protected $feedURL;

    public function __construct($feedURL) {
        $this->feedURL = $feedURL;
    }

    public function getFeedAsSimpleXML() {
        $feedData = $this->getFeed();
        if ($feedData) {
            return simplexml_load_string($feedData);
        } else {
            return false;
        }
    }

    public function getFeed() {
        return $this->get();
    }

    protected function get() {
        # ttl set to 6 hours
        $cache = new opCache(opRegexLib::rewriteFileName($this->feedURL), (360*60));
        if ($cache->isCache()) {
            return $cache->getCache();
        } else {
            $feedData = $this->curlFetch();
            if ($feedData) {
                return $cache->writeCache($feedData);
            } else {
                return false;
            }
        }
    }

    protected function curlFetch() {
        $cFetch = curl_init();

        curl_setopt($cFetch, CURLOPT_URL, $this->feedURL);
        curl_setopt($cFetch, CURLOPT_HEADER, 0);
        curl_setopt($cFetch, CURLOPT_RETURNTRANSFER, 1);

        $feed = curl_exec($cFetch);
        $cFetchInfo = curl_getinfo($cFetch);
        curl_close($cFetch);
        if ($cFetchInfo['http_code'] != 200) {
            return false;
        } else {
            return $feed;
        }
    }
}
?>
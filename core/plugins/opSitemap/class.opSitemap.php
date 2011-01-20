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
class opSitemap extends opPluginBase {   
    public static function getSitemap() {
        $db = opSystem::getDatabaseInstance();
        $rVal = $db->query('SELECT DISTINCT(plugin_id) FROM op_virtual_controller');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        $sArr = array();
        $sArr['opMenu'] = opMenu::getSitemap();
        foreach ($rVal->fetchAll() as $virtualController) {
            $pName = opPlugin::getNameById($virtualController['plugin_id']);
            if ($pName != 'opMenu') {
                $sArr[$pName] = call_user_func(array($pName, 'getSitemap'));
            }
        }
        return $sArr;
    }

    public static function getSitemapAsUL() {
        $opSitemap = '<ul id="opSitemap">';
        foreach (opSitemap::getSitemap() as $plugin) {
            if (is_array($plugin)) {
                foreach ($plugin as $itemName => $item) {
                    $opSitemap .= self::sitemapToUL($item);
                }
            }
        }
        return $opSitemap .= '</ul>';
    }

    protected static function sitemapToUL($sitemap) {
        $sitemapUL = '';
        $i = 0;
        foreach ($sitemap as $itemKey => $itemValue) {
            if (is_array($itemValue)) {
                foreach ($itemValue as $childKey => $childValue) {
                    if ($childValue['hide'] == 0) {
                        if (isset($childValue['url']) && $childValue['url'] !== false) {
                            $childLink = '<a href="'.$childValue['url'].'" title="'.$childValue['name'].'" target="'.$childValue['target'].'">'.$childValue['name'].'</a>';
                        } else {
                            $childLink = '<span>'.$childValue['name'].'</span>';
                        }
                        $sitemapUL .= '<li'.(((count($sitemap)-1) == $i) ? ' class="lastChild"' : '').'>'.$childLink;
                        if (is_array($childValue['childs']) && count($childValue['childs']) > 0) {
                            $sitemapUL .= '<ul'.((count($childValue['childs']) > 1) ? ' class="hasChildren"' : '').'>';
                            $sitemapUL .= self::sitemapToUL($childValue['childs']);
                            $sitemapUL .= '</ul>';
                        }
                        $sitemapUL .= '</li>';
                    }
                }
            }
            $i++;
        }
        return $sitemapUL;
    }
}
?>
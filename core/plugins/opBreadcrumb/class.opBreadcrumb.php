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
class opBreadcrumb extends opPluginBase {
    public static function getBreadcrumb() {
        $db         = opSystem::getDatabaseInstance();
        $router     = opSystem::getRouterInstance();
        $route      = $router->getArgs();
        $vc         = (isset($route[0])) ? $route[0] : false;

        # Find home link
        $rVal = $db->query('SELECT COUNT(*) FROM op_menu_items WHERE home = 1');
        $rVal->setFetchMode(PDO::FETCH_ASSOC);
        if ($rVal->fetchColumn() > 0) {
            $rVal = $db->query('SELECT * FROM op_menu_items WHERE home = 1');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $homeData  = $rVal->fetch();
            $homeHint  = $homeData['hint'];
            $homeTitle = $homeData['name'];
        } else {
            $homeHint  = 'No home set';
            $homeTitle = 'No home set';
        }

        # Build crumb
        if ($vc !== false) {
            $db = opSystem::getDatabaseInstance();
            $rVal = $db->prepare('SELECT COUNT(*) FROM op_virtual_controller WHERE controller = :controller');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('controller' => $vc));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $db->prepare('SELECT plugin_id FROM op_virtual_controller WHERE controller = :controller');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('controller' => $vc));
                $pluginData = $rVal->fetch();

                $plugin = opPlugin::getNameById($pluginData['plugin_id']);
                $vc = call_user_func(array($plugin, 'getBreadcrumb'));
            } else {
                $vc = false;
            }
        }

        $crumb = '';
        if ($vc !== false && count($vc) > 0) {
            $crumb = self::buildCrumb($vc);
        }
        return '<ul id="opBreadcrumb"><li><a href="/" title="'.$homeHint.'">'.$homeTitle.'</a></li>'.$crumb.'</ul>';
    }

    protected static function buildCrumb($arr) {
        $separator  = opSystem::_get('separator', 'opBreadcrumb');
        $separator  = (! $separator) ? '/' : $separator;

        $crumb = '';
        for ($i = 0; $i < count($arr); $i++) {
            if (is_array($arr[$i]) && count($arr[$i]) == 2) {
                $name = $arr[$i][0];
                $link = $arr[$i][1];
                if ($link !== false && $i < count($arr)-1) {
                    $crumb .= '<li><span>'.$separator.'</span><a href="'.$link.'" title="'.$name.'">'.$name.'</a></li>';
                } else {
                    $crumb .= '<li><span>'.$separator.'</span>'.$name.'</li>';
                }
            } else {
                $crumb .= '<li><span>'.$separator.'</span>Error in crumb</li>';
            }
        }

        return $crumb;
    }
}
?>
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
class opMenuULRender {
    private $html, $internalURLManager, $externalURLManager;

    public function __construct() {
        $this->internalURLManager = new opMenuURLManager();
        $this->externalURLManager = new opMenuExternalURLManager(opSystem::getDatabaseInstance());
    }

    public function generateUL($arr, $ulid, $ulclass, $ulactiveclass, $route) {
        $ulid = (strlen($ulid) > 0) ? ' id="'.$ulid.'"' : '';
        $ulclass = (strlen($ulclass) > 0) ? ' class="'.$ulclass.'"' : '';
        $this->html = '<ul'.$ulid.$ulclass.'>';
        $this->recursive(0, $arr, $ulactiveclass, $route);
        $this->html .= '</ul>';
        return $this->html;
    }

    private function recursive($id, $arr, $ulactiveclass, $activeLinks) {
        foreach ($arr as $k => $v) {
            if ($v['parent'] == $id) {
                $this->html .= '<li>';
                if (count($activeLinks) > 0) {
                    $activeCSS = (in_array($v['id'], $activeLinks, true)) ? ' class="'.$ulactiveclass.'"' : '';
                } else {
                    $activeCSS = ($v['home'] == 1) ? ' class="'.$ulactiveclass.'"' : '';
                }
                $hint = (strlen($v['hint']) > 0) ? ' title="'.htmlspecialchars($v['hint']).'"' : '';
                if ($v['type'] == 0) {
                    $this->html .= '<a href="#"'.$hint.$activeCSS.'><span>'.$v['name'].'</span></a>';
                } else if ($v['type'] == 1) {
                    $this->html .= '<a href="'.(($interalURL = $this->internalURLManager->getURL($v['url'])) ? $interalURL : '/').'"'.$hint.$activeCSS.'><span>'.$v['name'].'</span></a>';
                } else {
                    $this->html .= '<a href="'.$this->externalURLManager->getURL($v['url']).'" target="'.$v['target'].'"'.$hint.$activeCSS.'><span>'.$v['name'].'</span></a>';
                }
                foreach ($arr as $k2 => $v2) {
                    if ($v2['parent'] == $v['id']) {
                        $this->html .= '<ul>';
                        $this->recursive($v['id'], $arr, $ulactiveclass, $activeLinks);
                        $this->html .= '</ul>';
                        break;
                    }
                }
                $this->html .= '</li>';
            }
        }
    }
}
?>
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
class opFormElementLayout extends opFormElement {
    protected $db;
    protected $templateSelect;
    protected $restrictToParent;
    protected $layoutMapper;
    protected $templateMapper;
    protected $restrictToQuickpublish;

    public function __construct($name = null, $label = null, $templateSelect = false, $restrictToParent = false, $restrictToQuickpublish = false) {
        $this->setName($name);
        $this->setLabel($label);
        $this->addClass('form_txt');
        $this->templateSelect = $templateSelect;
        $this->restrictToParent = $restrictToParent;
        $this->restrictToQuickpublish = $restrictToQuickpublish;

        $this->db = opSystem::getDatabaseInstance();

        $this->layoutMapper = new opFormDataMapper($this->db);
        $this->layoutMapper->setTable('op_layouts');
        $this->layoutMapper->setFieldIDName('id');

        $this->templateMapper = new opFormDataMapper($this->db);
        $this->templateMapper->setTable('op_theme_templates');
        $this->templateMapper->setFieldIDName('id');
    }
  
    public function getHtml() {
        $autoBtn = '<script>$(document).ready(function() { $(\'#opLayoutDialog_'.$this->elementName.'\').dialog({modal: true, autoOpen: false, width: 500, height: 400}); });</script><span class="btn-refresh"><a href="#" onclick="$(\'#opLayoutDialog_'.$this->elementName.'\').dialog(\'open\');" class="form_btn_input" title="'.opTranslation::getTranslation('_select_layout').'"><span><img src="/themes/opAdmin/images/icons/layout-hf-3.png" width="16" height="16" border="0" alt="'.opTranslation::getTranslation('_select_layout').'" /></span></a></span>';
        return '<label for="' . $this->elementName . '">' . $this->elementLabel . '</label><span class="input-shadow"><input type="hidden" id="'.$this->elementName.'" name="'.$this->elementName.'" value="' . $this->getValue() . '" /><input type="text" class="'.implode(' ', $this->elementClass).'" disabled="true" id="fake_' . $this->elementName . '" name="fake_' . $this->elementName . '" value="' . $this->sanitizeFormData($this->getLayoutName()) . '"/></span><div id="opLayoutDialog_'.$this->elementName.'" style="display:none;" title="'.opTranslation::getTranslation('_layout_dialog').'">'.$this->getLayoutData($this->elementName).'</div>'.$autoBtn;
    }

    public function getValue() {
        $this->layoutMapper->setRowId($this->elementValue);
        $layoutData = $this->layoutMapper->fetchRow();
        
        if ($layoutData !== false) {
            if ($this->restrictToParent) {
                return $layoutData->theme_template.':'.$layoutData->parent;
            } else {
                return $layoutData->theme_template.':'.$layoutData->id;
            }
        }

        return false;
    }

    protected function getLayoutName() {
        $lVal = $this->getValue();
        if ($lVal !== false) {
            list($tId, $lId) = explode(':', $lVal);
            if ($lId > 0) {
                $this->layoutMapper->setRowId($lId);
                $layoutData = $this->layoutMapper->fetchRow();
                if ($layoutData !== false) {
                    return $layoutData->name;
                }
            } else {
                $this->templateMapper->setRowId($tId);
                $templateData = $this->templateMapper->fetchRow();
                if ($templateData !== false) {
                    return $templateData->name;
                }
            }
        }

        return false;
    }

    protected function getLayoutData($element) {
        # Themes
        $themes = $this->db->query('SELECT * FROM op_themes');
        $templateId = false;
        if ($this->restrictToParent !== false) {
            $this->layoutMapper->setRowId($this->elementValue);
            $layoutData = $this->layoutMapper->fetchRow();
            if ($layoutData !== false) {
                $templateId = $layoutData->theme_template;
                $this->templateMapper->setRowId($templateId);
                $templateData = $this->templateMapper->fetchRow();
                if ($templateData !== false) {
                    $themes = $this->db->query('SELECT * FROM op_themes WHERE id = '.$templateData->parent);
                }
            }
        }
        $themes->setFetchMode(PDO::FETCH_ASSOC);
        $themes = $themes->fetchAll();

        $layoutMap = '<ul id="opLayoutMap">';
        $x = 0;
        foreach ($themes as $theme) {
            # Theme
            $themeObj = new opTheme($theme['path']);

            # Templates
            if ($templateId !== false) {
                $templates = $this->db->query('SELECT * FROM op_theme_templates WHERE id = '.$templateId);
                $templates->setFetchMode(PDO::FETCH_ASSOC);
            } else {
                $templates = $this->db->prepare('SELECT * FROM op_theme_templates WHERE parent = :parent ORDER BY name ASC');
                $templates->setFetchMode(PDO::FETCH_ASSOC);
                $templates->execute(array('parent' => $theme['id']));
            }
            $templates = $templates->fetchAll();

            $layoutMap .= '<li'.((($x+1) == count($themes)) ? ' class="lastChild"' : '' ).'><img src="/themes/opAdmin/images/icons/tables-stacks.png" alt="theme" /> '.$themeObj->getName().'<ul class="hasChildren">';
            $i = 0;
            foreach ($templates as $template) {
                $layouts = $this->db->prepare('SELECT COUNT(*) FROM op_layouts WHERE theme_template = :tt');
                $layouts->setFetchMode(PDO::FETCH_ASSOC);
                $layouts->execute(array('tt' => $template['id']));
                
                $templateName = ($this->templateSelect) ? '<a href="#" onclick="$(\'#'.$element.'\').attr(\'value\', \''.$template['id'].':0\');$(\'#fake_'.$element.'\').attr(\'value\', \''.$template['name'].'\');$(\'#opLayoutDialog_'.$element.'\').dialog(\'close\');">'.$template['name'].'</a>' : $template['name'];

                $layoutMap .= '<li'.((($i+1) == count($templates)) ? ' class="lastChild"' : '' ).'><img src="/themes/opAdmin/images/icons/table.png" alt="template" /> '.$templateName;

                if ($layouts->fetchColumn() > 0) {
                    $layouts = $this->db->prepare('SELECT * FROM op_layouts WHERE theme_template = :tt AND parent = 0 ORDER BY name ASC');
                    $layouts->setFetchMode(PDO::FETCH_ASSOC);
                    $layouts->execute(array('tt' => $template['id']));
                    $layouts = $layouts->fetchAll();

                    $layoutMap .= $this->buildLayoutList($template['id'], $layouts, $element);
                }
                $layoutMap .= '</li>';

                $i++;
            }
            $layoutMap .= '</ul></li>';
            $x++;
        }
        $layoutMap .= '</ul>';

        return $layoutMap;
    }

    protected function buildLayoutList($templateId, $layouts, $element) {
        $layoutMap = '<ul class="hasChildren">';
        $j = 0;
        foreach ($layouts as $layout) {
            $selectable = true;
            if ($this->restrictToParent !== false && $layout['parent'] == $this->elementValue || $this->restrictToQuickpublish && $layout['type'] != 7) {
                $selectable = false;
            }
            $layoutId   = $templateId.':'.$layout['id'];
            $layoutName = ($selectable && $layout['id'] != $this->elementValue) ? '<a href="#" onclick="$(\'#'.$element.'\').attr(\'value\', \''.$layoutId.'\');$(\'#fake_'.$element.'\').attr(\'value\', \''.$layout['name'].'\');$(\'#opLayoutDialog_'.$element.'\').dialog(\'close\');">'.$layout['name'].'</a>' : $layout['name'];
            $layoutMap .= '<li'.((($j+1) == count($layouts)) ? ' class="lastChild"' : '' ).'><img src="/themes/opAdmin/images/icons/layout-hf-3.png" alt="layout" /> '.$layoutName;

            $rVal = $this->db->prepare('SELECT COUNT(*) FROM op_layouts WHERE parent = :id');
            $rVal->setFetchMode(PDO::FETCH_ASSOC);
            $rVal->execute(array('id' => $layout['id']));
            if ($rVal->fetchColumn() > 0) {
                $rVal = $this->db->prepare('SELECT * FROM op_layouts WHERE parent = :id ORDER BY name ASC');
                $rVal->setFetchMode(PDO::FETCH_ASSOC);
                $rVal->execute(array('id' => $layout['id']));
                $childLayouts = $rVal->fetchAll();
                $layoutMap .= $this->buildLayoutList($templateId, $childLayouts, $element);
            }
            $layoutMap .= '</li>';

            $j++;
        }
        $layoutMap .= '</ul>';

        return $layoutMap;
    }
}
?>
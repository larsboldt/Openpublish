<?php
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
function loopRecursively($opPluginName, $arr, $opTemplates, $opThemes, $opPluginPath, $templateName, $padding = 0) {
    foreach ($arr as $layout) {
        $childs = false;
        if (isset($layout[1])) {
            $childs = $layout[1];
            $layout = $layout[0];
        }
        $pArr = opLayout::getPluginsAssignedToLayout($layout['id']);

        #Set theme name
        $themeName = 'Unknown';
        foreach ($opTemplates as $template) {
            if ($template['id'] == $layout['theme_template']) {
                foreach ($opThemes as $theme) {
                    if ($template['parent'] == $theme['id']) {
                        $themeObj = new opTheme($theme['path']);
                        $themeName = $themeObj->getName();
                        break(2);
                    }
                }
            }
        }
        $tName  = (!$templateName) ? $layout['template_name'] : $templateName;
        $delete = (count($pArr) > 0 || opLayout::hasAssignedChilds($layout['id'])) ? '<img src="'.$opPluginPath.'icons/minus-circle-disable.png" width="16" height="16" alt="'.opTranslation::getTranslation('_not_delete_layout', $opPluginName).'" title="'.opTranslation::getTranslation('_not_delete_layout', $opPluginName).'" class="table-icon" />' : '<a href="javascript:$(\'#layoutID\').attr(\'value\', '.$layout['id'].');$(\'#formDeleteItem\').submit();" onclick="return confirm(\''.opTranslation::getTranslation('_delete_layout_warn_msg', $opPluginName).'\');"><img src="'.$opPluginPath.'icons/minus-circle.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_delete_layout', $opPluginName), '&quot;'.$layout['name'].'&quot;').'" title="'.sprintf(opTranslation::getTranslation('_delete_layout', $opPluginName), '&quot;'.$layout['name'].'&quot;').'" class="table-icon" /></a>';

        if ($layout['type'] > 0) {
            if ($layout['type'] == 1) {
                $layoutType = '404';
            } else if ($layout['type'] == 2) {
                $layoutType = '503';
            } else if ($layout['type'] == 3) {
                $layoutType = 'Site offline';
            } else if ($layout['type'] == 4) {
                $layoutType = 'RSS 2.0';
            } else if ($layout['type'] == 5) {
                $layoutType = 'RSS 1.0';
            } else if ($layout['type'] == 6) {
                $layoutType = 'Atom';
            }
            if ($layout['type'] > 3) {
                $assigned = (count($pArr) > 0) ? '<img src="'.$opPluginPath.'icons/feed.png" width="16" height="16" alt="'.$layoutType.'" title="'.$layoutType.'" class="table-icon" />' : '&nbsp;';
            } else {
                $assigned = '<img src="'.$opPluginPath.'icons/server--exclamation.png" width="16" height="16" alt="'.$layoutType.'" title="'.$layoutType.'" class="table-icon" />';
            }
        } else {
            if (count($pArr) > 0) {
                $assigned = '';
                foreach ($pArr as $pID => $pName) {
                    $c = call_user_func(array($pName, 'getConfig'));
                    $pN = (isset($c->name)) ? $c->name : 'Untitled';
                    $pI = call_user_func(array($pName, 'getIcon'));
                    if ($pI !== false) {
                        $assigned .= '<img src="'.$pI.'" width="16" height="16" alt="'.$pN.'" title="'.$pN.'" class="table-icon" />';
                    }
                }
            } else {
                $assigned = '&nbsp;';
            }
            
        }

        echo '<li '.((isset($_SESSION['opDesignMode_layoutID']) && $_SESSION['opDesignMode_layoutID'] == $layout['id']) ? ' class="activeItem"' : '').'>
                <dl>
                    <dt><div class="opAccordionHeader" style="padding-left: '.$padding.'px"><a href="/admindm/'.$layout['id'].'" title="'.sprintf(opTranslation::getTranslation('_enter_designmode', $opPluginName), '&quot;'.$layout['name'].'&quot;').'">'.$layout['name'].'</a></div></dt>
                    <dd class="listCol1"><a href="/admin/opLayout/layoutMetaEdit/'.$layout['id'].'" title="'.opTranslation::getTranslation('_edit_meta', $opPluginName).'"><img src="'.$opPluginPath.'icons/tags-label.png" class="table-icon" /></a></dd>
                    <dd class="listCol2">'.$tName.'</dd>
                    <dd class="listCol3">'.$themeName.'</dd>
                    <dd class="listCol4">'.$assigned.'</dd>
                    <dd class="listCol5"><a href="/admin/opLayout/layoutEdit/'.$layout['id'].'"><img src="'.$opPluginPath.'icons/pencil.png" width="16" height="16" alt="Edit '.$layout['name'].'" title="'.sprintf(opTranslation::getTranslation('_edit_layout', $opPluginName), '&quot;'.$layout['name'].'&quot;').'" class="table-icon" /></a>'.$delete.'</dd>
                </dl>';
        if (is_array($childs)) {
            echo '<ul>';
            loopRecursively($opPluginName, $childs, $opTemplates, $opThemes, $opPluginPath, $tName, $padding+20);
            echo '</ul>';
        }
        echo '</li>';
    }
}
?>
<h3><?php echo opTranslation::getTranslation('_layouts', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/layout-header-footer-3.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opLayout/layoutNew" title="<?php echo opTranslation::getTranslation('_new_layout', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/layout-plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_new_layout', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_new_layout', $opPluginName) ?></span></a>
    </span>
</h3>
<dl class="listHeader">
    <dt><?php echo opTranslation::getTranslation('_layout_name', $opPluginName) ?></dt>
    <dd class="listCol1"><?php echo opTranslation::getTranslation('_meta', $opPluginName) ?></dd>
    <dd class="listCol2">
        <select onchange="window.location='/admin/opLayout/adminIndexSortByTemplate/' + $(this).attr('value')">
            <option value="0"><?php echo opTranslation::getTranslation('_all_templates', $opPluginName) ?></option>
            <option value="-1">---------------</option>
            <?php
            foreach ($opTemplates as $v) {
                $s = ($opTemplateSort == $v['id']) ? ' selected="true"' : '';
                echo '<option value="'.$v['id'].'"'.$s.'>'.$v['name'].'</option>';
            }
            ?>
        </select>
    </dd>
    <dd class="listCol3">
        <select class="select-1st" onchange="window.location='/admin/opLayout/adminIndexSortByTheme/' + $(this).attr('value')">
            <option value="0"><?php echo opTranslation::getTranslation('_all_themes', $opPluginName) ?></option>
            <option value="-1">---------------</option>
            <?php
            foreach ($opThemes as $v) {
                $s = ($opThemeSort == $v['id']) ? ' selected="true"' : '';
                echo '<option value="'.$v['id'].'"'.$s.'>'.$v['theme_name'].'</option>';
            }
            ?>
        </select>
    </dd>
    <dd class="listCol4"><?php echo opTranslation::getTranslation('_assigned', $opPluginName) ?></dd>
    <dd class="listCol5"><?php echo opTranslation::getTranslation('_action', $opPluginName) ?></dd>
</dl>
<ul class="listBody">
    <?php
    loopRecursively($opPluginName, $opLayouts, $opTemplates, $opThemes, $opPluginPath, false);
    ?>
</ul>
<form id="formDeleteItem" method="post" action="/admin/opLayout/layoutDelete">
    <input type="hidden" id="layoutID" name="layoutID" value="" />
</form>
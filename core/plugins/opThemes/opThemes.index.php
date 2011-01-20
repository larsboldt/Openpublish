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
 ?>
<h3><?php echo opTranslation::getTranslation('_themes', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/map.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opThemes/refresh" title="<?php echo opTranslation::getTranslation('_update', $opPluginName) ?>" onclick="return confirm('<?php echo opTranslation::getTranslation('_update_warn_msg', $opPluginName)?>');" ><span><img src="<?php echo $opPluginPath ?>icons/arrow-circle-double.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_install_theme', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_update', $opPluginName) ?></span></a>
    </span>
</h3>
<table cellpadding="0" cellspacing="0" border="0" class="scheme">
    <thead>
        <tr>
            <td width="16" align="center" valign="top">&nbsp;</td>
            <td align="left" valign="top"><?php echo opTranslation::getTranslation('_theme', $opPluginName) ?></td>
            <td align="left" valign="top"><?php echo opTranslation::getTranslation('_templates', $opPluginName) ?></td>
            <td align="center"><?php echo opTranslation::getTranslation('_action', $opPluginName) ?></td>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($opThemes as $v) {
            $themeObj = new opTheme($v['path']);
            echo '<tr><td align="center" valign="top"><img src="'.$opPluginPath.'icons/map.png" class="table-icon" /></td><td align="left" valign="top">'.$themeObj->getName().'</td><td align="left" valign="top">';
            $templateList = '';
            foreach ($opTemplates as $template) {
                if ($template['parent'] == $v['id']) {
                    $templateList .= $template['name'].', ';
                }
            };
            echo substr($templateList, 0, -2);
            echo '</td><td width="16" align="center"><a href="/admin/opThemes/themeDelete/'.$v['id'].'" onclick="return confirm(\''.opTranslation::getTranslation('_uninstall_warn_msg', $opPluginName).'\');" title="'.opTranslation::getTranslation('_uninstall', $opPluginName).'"><img src="'.$opPluginPath.'icons/map--minus.png" alt="'.opTranslation::getTranslation('_uninstall', $opPluginName).'" /></a></td></tr>';
        }
        ?>
    </tbody>
</table>
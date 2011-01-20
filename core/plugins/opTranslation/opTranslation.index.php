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
?>
<h3><?php echo opTranslation::getTranslation('_translations', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/globe.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="/admin/opTranslation/translationAdd" title="<?php echo opTranslation::getTranslation('_add_translation', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/globe--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_add_translation', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_add_translation', $opPluginName) ?></span></a>
    </span>
</h3>
<table cellpadding="0" cellspacing="0" border="0" class="scheme">
    <thead>
        <tr>
            <td width="20">&nbsp;</td>
            <td><?php echo opTranslation::getTranslation('_name_en', $opPluginName) ?></td>
            <td><?php echo opTranslation::getTranslation('_name_na', $opPluginName) ?></td>
            <td><?php echo opTranslation::getTranslation('_translate', $opPluginName) ?></td>
            <td width="80"><?php echo opTranslation::getTranslation('_code', $opPluginName) ?></td>
            <td colspan="2" align="center"><?php echo opTranslation::getTranslation('_action', $opPluginName) ?></td>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 0;
        foreach ($opTranslations as $translation) {
            $rowClass = ($i % 2) ? 'odd' : 'even';
            echo '<tr class="'.$rowClass.'">';
            echo '<td width="20"><img src="'.$opPluginPath.'flags/'.$translation['code'].'.png" alt="'.$translation['name_en'].'" /></td>';
            echo '<td>'.$translation['name_en'].'</td>';
            echo '<td>'.$translation['name_na'].'</td>';
            echo '<td><a href="/admin/opTranslation/translate/'.$translation['id'].'" title="'.sprintf(opTranslation::getTranslation('_translate_lang', $opPluginName), '&quot;'.$translation['name_na'].'&quot;').'"><img src="'.$opPluginPath.'icons/globe--arrow.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_translate_lang', $opPluginName), '&quot;'.$translation['name_na'].'&quot;').'" /></a></td>';
            echo '<td width="80">'.$translation['code'].'</td>';
            echo '<td width="20" align="center"><a href="/admin/opTranslation/translationEdit/'.$translation['id'].'" title="'.sprintf(opTranslation::getTranslation('_edit_language', $opPluginName), '&quot;'.$translation['name_en'].'&quot;').'"><img src="'.$opPluginPath.'icons/globe--pencil.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_edit_language', $opPluginName), '&quot;'.$translation['name_en'].'&quot;').'" /></a></td>';
            echo '<td width="20" align="center"><a href="/admin/opTranslation/translationRemove/'.$translation['id'].'" onclick="return confirm(\''.sprintf(opTranslation::getTranslation('_delete_lang_warn_msg', $opPluginName), '&quot;'.$translation['name_en'].'&quot;').'\');" title="'.sprintf(opTranslation::getTranslation('_remove_language', $opPluginName), '&quot;'.$translation['name_en'].'&quot;').'"><img src="'.$opPluginPath.'icons/globe--minus.png" width="16" height="16" alt="'.sprintf(opTranslation::getTranslation('_remove_language', $opPluginName), '&quot;'.$translation['name_en'].'&quot;').'" /></a></td>';
            echo '</tr>';
            $i++;
        }
        ?>
    </tbody>
</table>
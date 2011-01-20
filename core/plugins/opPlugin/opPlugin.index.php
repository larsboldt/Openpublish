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
<h3><?php echo opTranslation::getTranslation('_plugins', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/puzzle.png" width="16" height="16" alt="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a class="btnset" href="/admin/opPlugin/pluginManualInstall" title="<?php echo opTranslation::getTranslation('_manual_install', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/puzzle--plus.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_manual_install', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_manual_install', $opPluginName) ?></span></a>
        <a class="btnset" href="/admin/opPlugin/pluginAdvancedControl" title="<?php echo opTranslation::getTranslation('_advanced_plugin_control', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/block.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_advanced_plugin_control', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_advanced_plugin_control', $opPluginName) ?></span></a>
        <a class="btnset" href="/admin/opPlugin/pluginSettings" title="<?php echo opTranslation::getTranslation('_settings', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/gear.png" width="16" height="16" alt="<?php echo opTranslation::getTranslation('_settings', $opPluginName) ?>" class="table-icon" /> <?php echo opTranslation::getTranslation('_settings', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="content-repos">
	<div id="content-sidebar">
    	<div class="filesHead"><?php echo opTranslation::getTranslation('_installed_plugins', $opPluginName) ?></div>
            <ul id="plugList">
            <?php
            foreach ($opPlugins as $aP) {
                $p = new $aP['plugin_name']($theme, null);
                $icon = ($p->getIcon()) ? $p->getIcon() : $opPluginPath.'icons/puzzle-small.png';
                echo '<li><a style="background: transparent url('.$icon.') no-repeat 10px 6px;" href="/admin/opPlugin/pluginManage/'.$aP['id'].'" title="'.sprintf(opTranslation::getTranslation('_manage_plugin', $opPluginName), '&quot;'.$aP['plugin_name'].'&quot;').'">'.$aP['plugin_name'].'</a></li>';
            }
            ?>
            </ul>
	</div>
    
    <div id="content-index">
            <div style="height: 300px">&nbsp;</div>
            <?php
            if (1 == 0) {
            ?>
            <div class="filesHead" style="height:30px;"><span style="float:left; padding: 3px 0 0 0;"><input value="Search..." class="form_txt_simple" type="text" value="<?php echo $opKeyword; ?>" name="kw" id="kw" /><a href="#" onclick="window.location='/admin/opPlugin/pluginSearch/' + $('#kw').attr('value');"><img src="<?php echo $opPluginPath; ?>icons/magnifier.png" class="table-icon" /></a></span><span style="float:right">Repository updated: 08.06.2009 16:54</span></div>
            <table cellpadding="0" cellspacing="0" border="0" width="80%" class="scheme-single">
                <thead>
                    <td align="left"><a href="/admin/opPlugin/pluginOrderBy/name/asc"><img src="<?php echo $opPluginPath; ?>icons/sort-alphabet.png" class="table-icon" /></a> Name <a href="/admin/opPlugin/pluginOrderBy/name/desc"><img src="<?php echo $opPluginPath; ?>icons/sort-alphabet-descending.png" class="table-icon" /></a></td>
                    <td align="left" width="100">
                        <select style="width:100px !important;" id="category" name="category" onchange="window.location='/admin/opPlugin/pluginFilter/' + $(this).attr('value');">
                        <option value="">- All categories -</option>
                        <?php
                        foreach ($opCategories as $v) {
                            $s = ($opFilter == $v['category']) ? ' selected="true"' : '';
                            echo '<option value="'.$v['category'].'"'.$s.'>'.$v['category'].'</option>';
                        }
                        ?>
                        </select>
                    </td>
                    <td align="center" width="100"><a href="/admin/opPlugin/pluginOrderBy/rating/asc"><img src="<?php echo $opPluginPath; ?>icons/sort-rating-descending.png" class="table-icon" /></a> Rating <a href="/admin/opPlugin/pluginOrderBy/rating/desc"><img src="<?php echo $opPluginPath; ?>icons/sort-rating.png" class="table-icon" /></a></td>
                    <td align="center" width="120"><a href="/admin/opPlugin/pluginOrderBy/downloads/asc"><img src="<?php echo $opPluginPath; ?>icons/sort-number.png" class="table-icon" /></a> Downloads <a href="/admin/opPlugin/pluginOrderBy/downloads/desc"><img src="<?php echo $opPluginPath; ?>icons/sort-number-descending.png" class="table-icon" /></a></td>
                    <td align="center" width="60">Version</td>
                    <td align="center" width="80">Action</td>
                </thead>
                <?php
                $iconRating = new opRatingToIcon();
                $iconRating->setEmptyIcon($opPluginPath.'icons/star-empty.png');
                $iconRating->setHalfIcon($opPluginPath.'icons/star-half.png');
                $iconRating->setFullIcon($opPluginPath.'icons/star.png');
                foreach ($opPluginRepo as $p) {
                    $icons = '';
                    foreach ($iconRating->convertToIcon($p['rating']) as $icon) {
                        $icons .= '<img src="'.$icon.'" />';
                    }
                    echo '<tr>
                            <td align="left"><a href="/admin/opPlugin/pluginView/'.$p['pid'].'">'.$p['name'].'</a></td>
                            <td align="left">'.$p['category'].'</td>
                            <td align="center">'.$icons.'</td>
                            <td align="right">'.number_format($p['downloads'], 0, '.', ',').'</td>
                            <td align="center">'.number_format(($p['version']/100), 2, '.', ',').'</td>
                            <td align="center"><a href="/admin/opPlugin/pluginInstall/'.$p['pid'].'"><img src="'.$opPluginPath.'images/btn_plugins_install.png" /></a></td>
                          </tr>';
                }
                ?>
                <tr>
                    <td colspan="6" class="pagination" align="left">
                        <?php
                        echo '<div class="page-show">';
                        echo '<strong>Showing:</strong> ';
                        $showTo = ($opCurrentPage*$opPageListingLimit)+$opPageListingLimit;
                        $showTo = ($showTo > $opPluginTotal) ? $opPluginTotal : $showTo;
                        echo (($opCurrentPage*$opPageListingLimit)+1).' - '.$showTo.' / '.$opPluginTotal;
                        echo '</div>';
                        echo '<div class="page-nav">';
                        echo '<strong>Pages:</strong> ';
                        echo '<a class="page-prev" href="#">&laquo;</a>';
                        for($i = 0; $i < $opPluginPages; $i++) {
                            $highlight = ($opCurrentPage == $i) ? ' class="page-active"' : '';
                            echo '<a'. $highlight .' href="/admin/opPlugin/pluginPage/'.$i.'">'. ($i+1) .'</a> ';
                        }
                        echo '<a class="page-next" href="#">&raquo;</a>';
                        echo '</div>';
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            }
            ?>
	</div>
</div>
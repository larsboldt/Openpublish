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
<h3><?php echo opTranslation::getTranslation('_create', $opPluginName) ?>
    <span class="heading-icon"><img src="<?php echo $opPluginPath ?>icons/wand.png" width="16" height="16" alt="" title="" class="table-icon" /></span>
    <span class="action-right-btns">
        <a href="<?php echo opURL::getUrl('/admin/opCreate/categoryIndex'); ?>" title="<?php echo opTranslation::getTranslation('_categories', $opPluginName) ?>"><span><img src="<?php echo $opPluginPath ?>icons/clear-folders.png" width="16" height="16" alt="" title="" class="table-icon" /> <?php echo opTranslation::getTranslation('_categories', $opPluginName) ?></span></a>
    </span>
</h3>
<div id="plug-cols">

    <div id="plug-col-1" class="plug-margin">
        <h4><?php echo opTranslation::getTranslation('_dashboard', $opPluginName) ?></h4>
        <ul id="opCreateList" class="equalize">
            <?php
            foreach ($opCategories as $k => $v) {
                echo '<li><a href="javascript://" class="arrow-down">'.$v['name'].'</a><ul id="'.$v['id'].'">';
                foreach ($opPlugins as $pK => $pV) {
                    $icon = ($pV[3]) ? 'background: url('.$pV[3].') no-repeat 0px 6px; padding: 0 0 0 22px;' : 'background: url(/themes/opAdmin/images/icons/puzzle.png) no-repeat 0px 6px; padding: 0 0 0 22px;';
                    if ($pV[0] == $v['id']) {
                        echo '<li class="item"><a href="'.opURL::getUrl('/admin/'.$pV[1]).'"><span style="'.$icon.'">'.$pV[2].'</span></a></li>';
                    }
                }
                echo '</ul></li>';
            }
            ?>
            <li><a href="javascript://" class="arrow-down"><?php echo opTranslation::getTranslation('_others', $opPluginName) ?></a><ul>
                    <?php
                    foreach ($opPlugins as $v) {
                        $icon = ($v[3]) ? 'background: url('.$v[3].') no-repeat 0px 6px; padding: 0 0 0 22px;' : 'background: url(/themes/opAdmin/images/icons/puzzle.png) no-repeat 0px 6px; padding: 0 0 0 22px;';
                        if ($v[0] == 0) {
                            echo '<li class="item"><a href="'.opURL::getUrl('/admin/'.$v[1]).'"><span style="'.$icon.'">'.$v[2].'</span></a></li>';
                        }
                    }
                    ?>
                </ul></li>
        </ul>
    </div><!--END col-1-->

    <div id="plug-col-2">
        <div class="sidebar-inner">
            <?php
            $fA = new opFeedAggregator('http://services.openpublish.org/news/security.xml');
            $xml = $fA->getFeedAsSimpleXML();
            if ($xml) {
                echo '<h5 class="h5-sidebar">'.$xml->channel->title.'</h5>';
                foreach ($xml->channel->item as $i) {
                    echo '<div class="news-item">';
                    echo '<h6>'.$i->title.'</h6>';
                    echo '<p>'.$i->description.'</p>';
                    echo '<p><a class="read-more" href="'.$i->link.'" title="'.$i->title.'" target="_blank">'.$i->title.'</a></p>';
                    echo '</div>';
                }
            } else {
                echo '<h5 class="h5-sidebar">'.opTranslation::getTranslation('_service_unavailable', $opPluginName).'</h5>';
            }
            ?>
        </div>
    </div><!--END col-2-->

</div><!--END plug-cols-->
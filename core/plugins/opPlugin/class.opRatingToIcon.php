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
class opRatingToIcon {
    protected $rating, $iconFull, $iconHalf, $iconEmpty;

    public function setFullIcon($icon) {
        if (is_file(DOCUMENT_ROOT.$icon)) {
            $this->iconFull = $icon;
        } else {
            die($icon.' does not exist');
        }
    }

    public function setHalfIcon($icon) {
        if (is_file(DOCUMENT_ROOT.$icon)) {
            $this->iconHalf = $icon;
        } else {
            die($icon.' does not exist');
        }
    }

    public function setEmptyIcon($icon) {
        if (is_file(DOCUMENT_ROOT.$icon)) {
            $this->iconEmpty = $icon;
        } else {
            die($icon.' does not exist');
        }
    }

    public function convertToIcon($rating) {
        $rating = intval($rating);
        if ($rating >= 0 && $rating <= 10) {
            $this->rating = $rating;
        } else {
            die('Rating value goes from 0 to 10');
        }
        if (!is_null($this->iconEmpty)
            && !is_null($this->iconHalf)
            && !is_null($this->iconFull)) {
            switch ($this->rating) {
                case 0:
                    return array($this->iconEmpty, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 1:
                    return array($this->iconHalf, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 2:
                    return array($this->iconFull, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 3:
                    return array($this->iconFull, $this->iconHalf, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 4:
                    return array($this->iconFull, $this->iconFull, $this->iconEmpty, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 5:
                    return array($this->iconFull, $this->iconFull, $this->iconHalf, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 6:
                    return array($this->iconFull, $this->iconFull, $this->iconFull, $this->iconEmpty, $this->iconEmpty);
                    break;
                case 7:
                    return array($this->iconFull, $this->iconFull, $this->iconFull, $this->iconHalf, $this->iconEmpty);
                    break;
                case 8:
                    return array($this->iconFull, $this->iconFull, $this->iconFull, $this->iconFull, $this->iconEmpty);
                    break;
                case 9:
                    return array($this->iconFull, $this->iconFull, $this->iconFull, $this->iconFull, $this->iconHalf);
                    break;
                case 10:
                    return array($this->iconFull, $this->iconFull, $this->iconFull, $this->iconFull, $this->iconFull);
                    break;
            }

        } else {
            return false;
        }
    }
}
?>
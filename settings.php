<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or 
// (at your option) any later vs
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This is the setting file for block_teacher_contact_card.
 *
 * @package    block_teacher_contact_card
 * @category   block
 * @copyright  2021 Song Kim
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_heading(
            'headerconfig',
            'General Settings',
            ''
        )
    );
    $settings->add(
        new admin_setting_configcheckbox(
            'block_teacher_contact_card/showassistants',
            get_string(
                'settingshowassistants',
                'block_teacher_contact_card'
            ),
            '',
            true,
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'block_teacher_contact_card/publiclist',
            get_string('settingpubliclist', 'block_teacher_contact_card'),
            get_string('settingpubliclistdesc', 'block_teacher_contact_card'),
            false
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'block_teacher_contact_card/showemail',
            get_string(
                'settingshowemail',
                'block_teacher_contact_card'
            ),
            '',
            true
        )
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'block_teacher_contact_card/shownumbers',
            get_string(
                'settingshownumbers',
                'block_teacher_contact_card'
            ),
            '',
            false
        )
    );

    $settings->add(
        new admin_setting_configcolourpicker(
            'block_teacher_contact_card/cardcolor',
            get_string('settingcardcolor', 'block_teacher_contact_card'),
            '',
            '#f0f8ff'
        )
    );
}

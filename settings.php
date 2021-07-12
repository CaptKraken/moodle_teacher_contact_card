<?php
if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_heading(
            'headerconfig',
            'General Settings',
            ''
        )
    );
    $settings->add(new admin_setting_configcheckbox('block_teacher_contact_card/showassistants', get_string('setting_showassistants', 'block_teacher_contact_card'), '', true,));

    $settings->add(new admin_setting_configcheckbox('block_teacher_contact_card/publiclist', get_string('setting_publiclist', 'block_teacher_contact_card'), get_string('setting_publiclistdesc', 'block_teacher_contact_card'), false));

    $settings->add(new admin_setting_configcheckbox('block_teacher_contact_card/showemail', get_string('setting_showemail', 'block_teacher_contact_card'), '', true));

    $settings->add(new admin_setting_configcheckbox('block_teacher_contact_card/shownumbers', get_string('setting_shownumbers', 'block_teacher_contact_card'), '', false));

    $settings->add(new admin_setting_configcolourpicker('block_teacher_contact_card/cardcolor', get_string('setting_cardcolor', 'block_teacher_contact_card'), '', '#f0f8ff'));
}

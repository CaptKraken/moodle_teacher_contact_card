<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This is the main class file for block_teacher_contact_card.
 *
 * @package    block_teacher_contact_card
 * @category   block
 * @copyright  2021 Song Kim
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_teacher_contact_card extends block_base
{
    public function init()
    {
        $this->title = get_string('teacher_contact_card', 'block_teacher_contact_card');
    }

    public function has_config()
    {
        return true;
    }

    // Only allow users to add the block when in a course page.
    public function applicable_formats()
    {
        return array('course-view' => true);
    }

    public function get_content()
    {
        global $USER, $CFG, $DB, $OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }
        // Importing css style and js script
        echo "<link rel='stylesheet' href='{$CFG->wwwroot}/blocks/teacher_contact_card/style/styles.css'>
        <script src='{$CFG->wwwroot}/blocks/teacher_contact_card/script/scripts.js' defer></script>";

        // Card color in settings
        $color = get_config('block_teacher_contact_card', 'cardcolor');
        echo "<style>.ti__list--item {
        background-color: {$color};
        }</style>";

        // Get strings
        $tagteacher = get_string('tagteacher', 'block_teacher_contact_card');
        $tagassistant = get_string('tagassistant', 'block_teacher_contact_card');
        $labelemail = get_string('labelemail', 'block_teacher_contact_card');
        $labelnumbers = get_string('labelnumbers', 'block_teacher_contact_card');
        $labelmessage = get_string('labelmessage', 'block_teacher_contact_card');
        $msgnoteacher = get_string('msgnoteacher', 'block_teacher_contact_card');
        $msgnopermission = get_string('msgnopermission', 'block_teacher_contact_card');

        // Functions

        /**
         * list all the users of a specific role in a course.
         * @param string $roleshortname string of role shortname from mdl_role
         * @param int $courseid id of the course
         * @return array an array of users
         */
        function get_list_of_users_by_role($roleshortname, $courseid)
        {
            global $DB;
            $role = $DB->get_record('role', array('shortname' => "{$roleshortname}"));
            $context  = context_course::instance($courseid);
            return get_role_users($role->id, $context);
        };

        /**
         * returns person's numbers, handle no number, one number, etc.
         * 
         * @param int|string|null $personphone $personobject->phone1;
         * @param int|string|null $personmobile $personobject->phone2;
         * @return string
         */
        function validate_numbers($personphone, $personmobile)
        {
            $msgnonumber = get_string('msgnonumber', 'block_teacher_contact_card');
            $msgnonumber = $msgnonumber ? $msgnonumber : 'No number on record.';

            // If EITHER phone OR mobile number exists, show.
            if ($personphone xor $personmobile) {
                $personnumbers = $personphone ? $personphone : $personmobile;
            }

            // If BOTH phone AND mobile exist: 
            if ($personphone && $personmobile) {
                // If the phone and mobile numbers are the same, show one.
                if ($personphone === $personmobile) $personnumbers = $personphone;
                // If not, show both
                $personnumbers = "{$personphone}<br>{$personmobile}";
            }

            // If NEITHER exists
            if (empty($personphone) && empty($personmobile)) $personnumbers = $msgnonumber;

            return $personnumbers;
        }

        // Get course id
        $courseid = $this->page->course->id;

        // Check if admin or enrolled
        if ($this->page->pagelayout === 'course') {
            $currentuserid = $USER->id;
            $isenrolled = false;
            $isadmin = false;

            // If there is a course id
            if (!empty($courseid)) {
                $context  = context_course::instance($courseid);
                $students = get_enrolled_users($context);

                // If enrolled
                foreach ($students as $key => $value) {
                    if ($value->id === $currentuserid) {
                        $isenrolled = true;
                        break;
                    } else $isenrolled = false;
                }

                // If admin
                $admins = get_admins();
                foreach ($admins as $key => $value) {

                    if ($value->id === $currentuserid) {
                        $isadmin = true;
                        break;
                    } else $isadmin = false;
                }
            }
        }

        // Getting all teachers from the course
        $teachers = get_list_of_users_by_role('editingteacher', $courseid);
        // Getting all teacher assistants from the course
        $teacherassistants  = get_list_of_users_by_role('teacher', $courseid);

        $showassitants = get_config('block_teacher_contact_card', 'showassistants');
        // If show assistants is enabled in settings:
        if ($showassitants) {
            // Show assistants
            $allteachers = $teachers + $teacherassistants;
        } else {
            // Show teachers only
            $allteachers = $teachers;
        }

        $showemail = get_config('block_teacher_contact_card', 'showemail');
        $shownumbers = get_config('block_teacher_contact_card', 'shownumbers');

        $html = '<section class="ti"><ul class="ti__list">';
        $i = 1;

        foreach ($allteachers as $id => $info) {

            // Getting user object
            $personobject = core_user::get_user($id);
            $personfullname = "$personobject->firstname $personobject->lastname";
            $personemail = $personobject->email;
            $personphone = $personobject->phone1;
            $personmobile = $personobject->phone2;

            $personnumbers = validate_numbers($personmobile, $personphone);

            // Displaying user picture
            // My moodle discussion thread: https://moodle.org/mod/forum/discuss.php?d=424270
            $conditions = array('size' => '240', 'link' => false, 'class' => '');
            $personprofilepic = $OUTPUT->user_picture($personobject, $conditions);

            // Roleid: 3 = editing teacher, 4 = non-editing teacher (db, mdl_role)
            if ((int)$info->roleid === 3) {
                $role = $tagteacher;
            }
            if ((int)$info->roleid === 4) {
                $role = $tagassistant;
            }

            // Get message link
            $messageurl = $CFG->wwwroot . "/message/index.php?id={$id}";

            // Hidden class if not the first card.
            $hidden = $i !== 1 ? 'hide' : '';
            // Different collapse icon because the first on isn't hidden
            $colapseicon = $i === 1 ? "▲" : "▼";

            // Show email if show email is enabled in settings
            $emailsection = $showemail ? "
            <div>
            <p>📧 {$labelemail}</p>
            <p>{$personemail}</p>
            </div>"
                : "";

            // Show numbers if show numbers is enabled in settings
            $numberssection = $shownumbers ? "
            <div>
                <p>📞 {$labelnumbers}</p>
                <p>{$personnumbers}</p>
            </div>"
                : "";

            $html .= "
                <li class='ti__list--item'>
                    <p class='ti__head'><span>{$personfullname}</span> <span class='ti__collapse-icon'>{$colapseicon}</span></p>
                    
                    <article class='ti__card {$hidden}'>  
                        <div class='ti__card__img'>
                        {$personprofilepic}
                        </div>                  
                        <div class='card__content'>
                            <p class='ti__role'>{$role}</p>
                            {$emailsection}
                            {$numberssection}
                            <div>
                            ✉️ <a class='msg-btn' href='{$messageurl}'>{$labelmessage} {$personfullname}</a>
                            </div>
                        </div>
                    </article>    

                </li>
                ";

            $i += 1;
        }

        $html .= '</ul></section>';

        $this->content =  new stdClass;
        $this->content->text = $html;
        // $this->content->footer = 'Footer here...';

        // If theres's no teacher, show no teacher message.
        if (count($allteachers) === 0) $this->content->text = $msgnoteacher;

        // If public teacher list enable:
        $publiclist = get_config('block_teacher_contact_card', 'publiclist');
        if ($publiclist) {
            // Show teacher list to the public
            return;
        } else {
            // If not, dont.
            if (isguestuser()) $this->content->text = $msgnopermission;
        }

        // If user isn't enrolled and isn't admin, show no permission message.
        if ($isenrolled === false && $isadmin === false) $this->content->text = $msgnopermission;

        return $this->content;
    }
}

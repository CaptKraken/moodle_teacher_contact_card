<?php
class block_teacher_contact_card extends block_base {
    public function init() {
        $this->title = get_string('teacher_contact_card', 'block_teacher_contact_card');
    }

    public function has_config()
    {
        return true;
    }

    public function get_content() {
        global $USER, $CFG, $DB, $OUTPUT, $PAGE;
        if ($this->content !== null) {
          return $this->content;
        }
        // importing css style and js script
        echo "<link rel='stylesheet' href='{$CFG->wwwroot}/blocks/teacher_contact_card/styles.css'>
        <script src='{$CFG->wwwroot}/blocks/teacher_contact_card/scripts.js' defer></script>";

        // card color in settings
        $color = get_config('block_teacher_contact_card', 'cardcolor');
        echo "<style>.ti__list--item {
        background-color: {$color};
        }</style>";

        // get strings
        $tag_teacher = get_string('tag_teacher', 'block_teacher_contact_card');
        $tag_assistant = get_string('tag_assistant', 'block_teacher_contact_card');
        $label_email = get_string('label_email', 'block_teacher_contact_card');
        $label_numbers = get_string('label_numbers', 'block_teacher_contact_card');
        $label_message = get_string('label_message', 'block_teacher_contact_card');
        $msg_no_teacher = get_string('msg_no_teacher', 'block_teacher_contact_card');
        $msg_no_permission = get_string('msg_no_permission', 'block_teacher_contact_card');
        $msg_not_course_page = get_string('msg_not_course_page', 'block_teacher_contact_card');

        // functions
        /**
         * list all the users of a specific role in a course
         * @param string $role_shortname string of role shortname from mdl_role
         * @param int $courseID id of the course
         * @return array an array of users
         */
        function get_list_of_users_by_role($role_shortname, $courseID)
        {
            global $DB;
            $role = $DB->get_record('role', array('shortname' => "{$role_shortname}"));
            $context  = context_course::instance($courseID);
            return get_role_users($role->id, $context);
        };

        /**
         * returns person's numbers, handle no number, one number, etc.
         * 
         * @param int|string|null $person_phone $person_object->phone1;
         * @param int|string|null $person_mobile $person_object->phone2;
         * @return string
         */
        function validate_numbers($person_phone, $person_mobile)
        {
            $msg_no_number = get_string('msg_no_number', 'block_teacher_contact_card');
            $msg_no_number = $msg_no_number ? $msg_no_number : 'No number on record.';

            // if EITHER phone OR mobile number exists, show.
            if ($person_phone xor $person_mobile) {
                $person_numbers = $person_phone ? $person_phone : $person_mobile;
            }
            
            // if both phone AND mobile exist: 
            if ($person_phone && $person_mobile) {

                // if the phone and mobile numbers are the same, show one.
                if ($person_phone === $person_mobile) $person_numbers = $person_phone;
                // if not, show both
                $person_numbers = "{$person_phone}<br>{$person_mobile}";

            }

            // if NEITHER exists
            if (empty($person_phone) && empty($person_mobile)) $person_numbers = $msg_no_number;
                
            return $person_numbers;
        }

        //get course id
        $courseID = $PAGE->course->id;

        // check if admin or enrolled
        if ($PAGE->pagelayout === 'course'){
            $current_user_id = $USER->id;
            $isEnrolled = false;
            $isAdmin = false;
            
            // if there is a course id
            if (!empty($courseID)) {
                $context  = context_course::instance($courseID);
                $students = get_enrolled_users($context);
                
                // if enrolled
                foreach ($students as $key => $value) {
                    if ($value->id === $current_user_id) {
                        $isEnrolled = true; 
                        break;
                    }
                    else $isEnrolled = false;
                }    
                
                // if admin
                $admins = get_admins();
                foreach ($admins as $key => $value) {
                    
                    if ($value->id === $current_user_id) {
                        $isAdmin = true;
                        break;
                    }
                    else $isAdmin = false;
                }
            }
        }

        // getting all teachers from the course
        $teachers = get_list_of_users_by_role('editingteacher', $courseID);
        // getting all teacher assistants from the course
        $teacher_assistants  = get_list_of_users_by_role('teacher', $courseID);

        $showAssitants = get_config('block_teacher_contact_card', 'showassistants');
        // if show assistants is enabled in settings:
        if ($showAssitants) {
            // show assistants
            $all_teachers = $teachers + $teacher_assistants;
        } else {
            // show teachers only
            $all_teachers = $teachers;
        }

        $show_email = get_config('block_teacher_contact_card', 'showemail');
        $show_numbers = get_config('block_teacher_contact_card', 'shownumbers');


        $html = '<section class="ti"><ul class="ti__list">';
        $i = 1;

        foreach ($all_teachers as $id => $info) {
            
            // getting user object
            $person_object = core_user::get_user($id);
            $person_fullname = "$person_object->firstname $person_object->lastname";
            $person_email = $person_object->email;
            $person_phone = $person_object->phone1;
            $person_mobile = $person_object->phone2;

            $person_numbers = validate_numbers($person_mobile, $person_phone);

            // displaying user picture
            // my moodle discussion thread: https://moodle.org/mod/forum/discuss.php?d=424270
            $conditions = array('size' => '240', 'link' => false, 'class' => '');
            $person_profile_pic =  $OUTPUT->user_picture($person_object, $conditions);

            // roleid: 3 = editing teacher, 4 = non-editing teacher (db, mdl_role)
            if ((int)$info->roleid === 3) $role = $tag_teacher;
            if ((int)$info->roleid === 4) $role = $tag_assistant;

            // get message link
            $message_url = $CFG->wwwroot . "/message/index.php?id={$id}";

            // hidden class if not the first card.
            $hidden = $i !== 1 ? 'hide' : '';
            // different collapse icon because the first on isn't hidden
            $colapse_icon = $i === 1 ? "▲" : "▼";

            // show email if show email is enabled in settings
            $email_section = $show_email ? "
            <div>
            <p>📧 {$label_email}</p>
            <p>{$person_email}</p>
            </div>"
            : "";
            
            // show numbers if show numbers is enabled in settings
            $numbers_section = $show_numbers ? "
            <div>
                <p>📞 {$label_numbers}</p>
                <p>{$person_numbers}</p>
            </div>"
                : "";

            $html .= "
                <li class='ti__list--item'>
                    <p class='ti__head'><span>{$person_fullname}</span> <span class='ti__collapse-icon'>{$colapse_icon}</span></p>
                    
                    <article class='ti__card {$hidden}'>  
                        <div class='ti__card__img'>
                        {$person_profile_pic}
                        </div>                  
                        <div class='card__content'>
                            <p class='ti__role'>{$role}</p>
                            {$email_section}
                            {$numbers_section}
                            <div>
                            ✉️ <a class='msg-btn' href='{$message_url}'>{$label_message} {$person_fullname}</a>
                            </div>
                        </div>
                    </article>    

                </li>
                ";

            $i += 1;
        }

        $html .= '</ul></section>';

        $this->content         =  new stdClass;
        $this->content->text   = $html;
        // $this->content->footer = 'Footer here...';

        // if theres's no teacher, show no teacher message.
        if (count($all_teachers) === 0) $this->content->text = $msg_no_teacher;
        
        // if public teacher list enable:
        $public_list = get_config('block_teacher_contact_card', 'publiclist');
        if ($public_list) {
            // show teacher list to the public
            return;
        } else {
            // if not, dont.
            if (isguestuser()) $this->content->text = $msg_no_permission;
        }

        // if the current page isn't a course page, show not a course page message.
        if ($PAGE->pagelayout !== 'course') $this->content->text = $msg_not_course_page;
        
        // if user isn't enrolled and isn't admin, show no permission message.
        if ($isEnrolled===false && $isAdmin===false) $this->content->text = $msg_no_permission;

        return $this->content;
    }
}
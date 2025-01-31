<?php
function scoreInPercentage($numerator, $denominator)
{
    if ($denominator > 0) {
        return ($numerator / $denominator) * 100;
    }
    return 0;
}
function rainbowColor($color)
{
    $arr = [
        "1" =>    "bg-blue",
        "2" =>    "bg-orange",
        "3" =>    "bg-green",
        "4" =>    "bg-red",
        "5" =>    "bg-yellow",
        "6" =>    "bg-brown",
        "7" =>    "bg-pink",

    ];

    if ($color) {
        return $arr[$color];
    }
    return $arr;
}

if (!function_exists('schoolOfSubdomain')) {
    /**
     * Check the existence of subdomain
     *
     * @param null $subdomain
     * @return \App\Student
     */

    function schoolOfSubdomain($subdomain = null)
    {
        $subdomain = $subdomain ?: request()->route('subdomain');
        $school = \App\School::where('sub_domain', $subdomain)->first();
        if ($school) {
            return $school;
        }
        return false;
    }
}

/**
 * Delete Message
 * @return String
 */
function formatToTwoDecimalPlaces($gpa)
{
    return sprintf("%01.2f", $gpa);
}

function deleteMessage()
{
    return 'yes';
}
/**
 * @param null $status
 * @return array|mixed
 */
function status($status = null)
{
    $arr = [
        0 => 'De-active',
        1 => 'Active'
    ];
    if ($status !== null) {
        return $arr[$status];
    }
    return $arr;
}

function feeFecurrence($status = null)
{
    $arr = [
        'Termly' => 'Every Term',
        'Once Per Session' => 'Once Per Session'

    ];
    if ($status !== null) {
        return $arr[$status];
    }
    return $arr;
}

/**
 * @param null $status
 * @return array|mixed
 */
function assignmentStatus($status = null)
{
    $arr = [
        0 => 'Stale',
        1 => 'Active'
    ];
    if ($status !== null) {
        return $arr[$status];
    }
    return $arr;
}
/**
 * @param $status
 * @return string
 */
function getFormattedStatus($status, $task = 'default')
{
    $class = getClass($status); //($status == 1) ? 'success' : 'danger';
    $html = '<i class="fa fa-circle ' . $class . '"></i> ';
    if ($task == 'default') {
        $html .= status($status);
    } else {
        $html .= assignmentStatus($status);
    }

    return $html;
}

function formatLevel($level)
{
    $level = str_replace('-', ' ', $level);
    $level = ucwords($level);

    return $level;
}
/**
 * @param $level
 * @return array|mixed
 */
function severityLevels($level = null)
{
    $arr = [
        ''  => 'Select Level',
        'good' => 'Good',
        'fair' => 'Fair',
        'severe' => 'Severe',
        'terrible' => 'Terrible'
    ];
    if ($level !== null) {
        return $arr[$level];
    }
    return $arr;
}


/**
 * @param null $relation
 * @return array|mixed
 */
function relationships($relation = null)
{
    $arr = [
        'parent' => 'Parent',
        'guardian' => 'Guardian'
    ];

    if ($relation) {
        return $arr[$relation];
    }
    return $arr;
}

/**
 * @param $status
 * @return string
 */
function getClass($status)
{
    return ($status == 1) ? 'success green' : 'danger red';
}

/**
 * @param $status
 * @return string
 */
function getFaClass($status)
{
    return ($status == 1) ? 'close' : 'key';
}

/**
 * @param $status
 * @return string
 */
function getLinkText($status)
{
    return ($status == 1) ? 'Deactivate' : 'Activate';
}

function curricula()
{
    return [
        'British', 'Nigerian'
    ];
}
function curriculumLevel($curriculum = null, $level_group = null, $level = null)
{
    $arr = [
        'british' => [
            'Pre School' => [
                'Creche' => 'Creche',
                'Play Group' => 'Play Group',
                'Nur 1' => 'Nursery One',
                'Nur 2' => 'Nursery Two',
                'Pre-Kindergarten' => 'Pre-Kindergarten',
                'Kindergarten' => 'Kindergarten',
            ],
            'Junior School' => [
                'Yr 1' => 'Year One',
                'Yr 2' => 'Year Two',
                'Yr 3' => 'Year Three',
                'Yr 4' => 'Year Four',
                'Yr 5' => 'Year Five',
                'Yr 6' => 'Year Six',
            ],
            'Senior School' => [
                'Yr 7' => 'Year Seven',
                'Yr 8' => 'Year Eight',
                'Yr 9' => 'Year Nine',
                'Yr 10' => 'Year Ten',
                'Yr 11' => 'Year Eleven',
                'Yr 12' => 'Year Twelve',
            ],
            'After School' => [
                'Alumni' => 'Alumni',
                'A Level' => 'A Level',
                'Aspire' => 'Aspire'
            ],
        ],
        'british-main' => [
            'Pre School' => [
                'Creche' => 'Creche',
                'Play Group' => 'Play Group',
                'Nur 1' => 'Nursery One',
                'Nur 2' => 'Nursery Two',
                'Pre-Kindergarten' => 'Pre-Kindergarten',
                'Kindergarten' => 'Kindergarten',
            ],
            'Junior School' => [
                'Yr 1' => 'Year One',
                'Yr 2' => 'Year Two',
                'Yr 3' => 'Year Three',
                'Yr 4' => 'Year Four',
                'Yr 5' => 'Year Five',
                'Yr 6' => 'Year Six',
            ],
            'High School' => [
                'Yr 7' => 'Year Seven',
                'Yr 8' => 'Year Eight',
                'Yr 9' => 'Year Nine',
                'Yr 10' => 'Year Ten',
                'Yr 11' => 'Year Eleven',
                'Yr 12' => 'Year Twelve',
            ],
            'After School' => [
                'Alumni' => 'Alumni',
                'A Level' => 'A Level',
                'Aspire' => 'Aspire'
            ],
        ],
        'nigerian' => [

            'Nursery' => [
                'Creche' => 'Creche',
                'KG 1' => 'Kindergarten One',
                'KG 2' => 'Kindergarten Two',
                'Reception' => 'Reception',
                'Play Grp' => 'Play Group',
                'Pre Nur' => 'Pre Nursery',
                'Nur 1' => 'Nursery One',
                'Nur 2' => 'Nursery Two',


            ],
            'Primary' => [
                'Pry 1' => 'Primary One',
                'Pry 2' => 'Primary Two',
                'Pry 3' => 'Primary Three',
                'Pry 4' => 'Primary Four',
                'Pry 5' => 'Primary Five',
                'Pry 6' => 'Primary Six',
            ],
            'Junior Secondary' => [
                'JSS 1' => 'Junior Secondary School One',
                'JSS 2' => 'Junior Secondary School Two',
                'JSS 3' => 'Junior Secondary School Three',

            ],
            'Senior Secondary' => [
                'SSS 1' => 'Senior Secondary School One',
                'SSS 2' => 'Senior Secondary School Two',
                'SSS 3' => 'Senior Secondary School Three',
            ],


        ],
        'american' => [],
        'spanish' => [],

    ];

    if ($curriculum && $level_group && $level) {
        return $arr[$curriculum][$level_group][$level];
    } else if ($curriculum && $level_group && !$level) {
        return $arr[$curriculum][$level_group];
    } else if ($curriculum && !$level_group && !$level) {
        return $arr[$curriculum];
    }
    return ['No curriculum supplied'];
}

/**
 * @param null $level
 * @return array|mixed
 */
function getBritishClasses($level = null)
{
    $arr = [
        'play-group' => 'Play Group',
        'pre-nursery' => 'Pre Nursery',
        'nursery' => 'Nursery',
        'reception' => 'Reception',
        'year-one' => 'Year One',
        'year-two' => 'Year Two',
        'year-three' => 'Year Three',
        'year-four' => 'Year Four',
        'year-five' => 'Year Five',
        'year-six' => 'Year Six',
        'year-seven' => 'Year Seven',
        'year-eight' => 'Year Eight',
        'year-nine' => 'Year Nine',
        'year-ten' => 'Year Ten',
        'year-eleven' => 'Year Eleven',
        'alumni' => 'Alumni',
        'a-level' => 'A Level',
        'aspire' => 'Aspire'
    ];

    if ($level) {
        return $arr[$level];
    }
    return $arr;
}

/**
 * @param null $level
 * @return array|mixed
 */
function getPrecedingLevels($level = null)
{
    $arr = [
        'none' => 'None',
        'play-group' => 'Play Group',
        'pre-nursery' => 'Pre Nursery',
        'nursery' => 'Nursery',
        'reception' => 'Reception',
        'year-one' => 'Year One',
        'year-two' => 'Year Two',
        'year-three' => 'Year Three',
        'year-four' => 'Year Four',
        'year-five' => 'Year Five',
        'year-six' => 'Year Six',
        'year-seven' => 'Year Seven',
        'year-eight' => 'Year Eight',
        'year-nine' => 'Year Nine',
        'year-ten' => 'Year Ten',
        'year-eleven' => 'Year Eleven',
        'a-level' => 'A Level'
    ];

    if ($level) {
        return $arr[$level];
    }
    return $arr;
}

/**
 * @param null $group
 * @return array|mixed
 */
function subjectGroups($group = null)
{
    $arr = [
        '' => 'Subject Group (If appl.)',
        '1' => 'LITERACY',
        '2' => 'NUMERACY',
        '3' => 'READING',
        '4' => 'WRITING',
        '5' => 'LEARNING SUPPORT',
        '6' => 'COMMUNICATION AND LANGUAGE',
        '7' => 'PHYSICAL DEVELOPMENT',
        '8' => 'PERSONAL, SOCIAL AND EMOTIONAL DEVELOPMENT',
        '9' => 'UNDERSTANDING THE WORLD',
        '10' => 'MATHEMATICS',
        '11' => 'SCIENCE'
    ];

    if ($group) {
        return $arr[$group];
    }
    return $arr;
}

function defaultComment($total)
{
    $comment = "";
    if ($total >= 75 && $total <= 100) {
        $comment = "Keep it up. Excellent Performance";
    } else if ($total >= 70 && $total <= 74.99) {
        $comment = "Aim higher. Outstanding Performance";
    } else if ($total >= 65 && $total <= 69.99) {
        $comment = "Do not relax. Impressive Performance";
    } else if ($total >= 60 && $total <= 64.99) {
        $comment = "Put a little more effort. Good Performance";
    } else if ($total >= 55 && $total <= 59.99) {
        $comment = "You can do better. Satisfactory Performance";
    } else if ($total >= 50 && $total <= 54.99) {
        $comment = "You can do better. Average Performance";
    } else if ($total >= 45 && $total <= 49.99) {
        $comment = "Put in more effort. Fair Performance";
    } else if ($total >= 40 && $total <= 44.99) {
        $comment = "Put in more effort. Fair Performance";
    } else {
        $comment = "Sit up and be serious. Poor Performance";
    }

    return $comment;
}



function defaultGradeColor($total)
{
    $color = "#ec4b0b";
    $grade = 'F';
    $grade_point = 0;
    $interpretation = 'Fail';
    if ($total >= 85 && $total <= 100) {
        $color = "#149e0a";
        $grade = 'A+';
        $grade_point = 5;
        $interpretation = 'Excellent';
    } else if ($total >= 70 && $total <= 84.99) {
        $color = "#47f43a";
        $grade = 'A';
        $grade_point = 4.5;
        $interpretation = 'Exemplary';
    } else if ($total >= 65 && $total <= 69.99) {
        $color = "#80f677";
        $grade = 'B+';
        $grade_point = 4.0;
        $interpretation = 'Very Good';
    } else if ($total >= 60 && $total <= 64.99) {
        $color = "#acf76f";
        $grade = 'B';
        $grade_point = 3.5;
        $interpretation = 'Good';
    } else if ($total >= 55 && $total <= 59.99) {
        $color = "#b8ee12";
        $grade = 'C+';
        $grade_point = 3;
        $interpretation = 'Good';
    } else if ($total >= 50 && $total <= 54.99) {
        $color = "#edef1c";
        $grade = 'C';
        $grade_point = 2.5;
        $interpretation = 'Average';
    } else if ($total >= 45 && $total <= 49.99) {
        $color = "#efc01c";
        $grade = 'D';
        $grade_point = 2;
        $interpretation = 'Fair';
    } else if ($total >= 40 && $total <= 44.99) {
        $color = "#ef931c";
        $grade = 'E';
        $grade_point = 1;
        $interpretation = 'Fair';
    }

    return array($grade, $color, $grade_point, $interpretation);
}


/**
 * @param string $name
 * @param array $result_details
 * @return string |comment
 */

function analyseAndCommentResult($name, $result_details)
{
    $_90_100_scores = $_80_89_scores = $_70_79_scores = $_65_69_scores = $_60_64_scores = $_50_59_scores = $_45_49_scores = $_40_44_scores = $_0_39_scores = "";

    foreach ($result_details as $subject => $score) {
        if ($score >= 90 && $score <= 100) {
            $_90_100_scores .= $subject . ", ";
        } else if ($score >= 80 && $score <= 89) {
            $_80_89_scores .= $subject . ", ";
        } else if ($score >= 70 && $score <= 79) {
            $comment .= $subject . ", ";
        } else if ($score >= 65 && $score <= 69) {
            $comment .= $subject . ", ";
        } else if ($score >= 60 && $score <= 64) {
            $comment .= $subject . ", ";
        } else if ($score >= 50 && $score <= 59) {
            $comment .= $subject . ", ";
        } else if ($score >= 45 && $score <= 49) {
            $comment .= $subject . ", ";
        } else if ($score >= 40 && $score <= 44) {
            $comment .= $subject . ", ";
        } else {
            $comment .= $subject . ", ";
        }
    }
}
function defaultGrades()
{
    $arr = [
        'A+' => ['85-100', 'Excellent'],
        'A' => ['70-84.99', 'Outstanding'],
        'B+' => ['65-69.99', 'Very Good'],
        'B' => ['60-64.99', 'Good'],
        'C+' => ['55-59.99', 'Credit'],
        'C' => ['50-54.99', 'Average'],
        'D' => ['45-49.99', 'Fair'],
        'E' => ['40-44.99', 'Fair'],
        'F' => ['0-39.99', 'Poor'],

    ];

    return $arr;
}

/**
 * @param null $effort
 * @return array|mixed
 */
function ratings($effort = null, $option = null)
{
    $arr = [
        //'' => 'Select',
        5 => 'Excellent',
        4 => 'Very Good',
        3 => 'Good',
        2 => 'Average',
        1 => 'Fair',
    ];

    if ($effort && $option) {
        return $arr[$effort];
    } else if ($effort == null && $option) {
        return '';
    }
    return $arr;
}

/**
 * @param null $behavior
 * @return array|mixed
 */
function classBehavior($behavior = null, $option = null)
{
    $arr = [
        '' => 'Select',
        '1' => 'Excellent',
        '2' => 'Very Good',
        '3' => 'Good',
        '4' => 'Normal',
        '5' => 'Fair',
    ];


    if ($behavior && $option) {
        return $arr[$behavior];
    } else if ($behavior == null && $option) {
        return '';
    }
    return $arr;
}

/**
 * @param null $role
 * @return array|mixed
 */
function getSuperSetRoles($role = null)
{
    $arr = [
        'admin' => 'School Admin',

        /* 'instructor' => 'Instructor',
        'vice principal' => 'Vice Principal',
        'deputy head' => 'Deputy Head'*/
    ];

    if ($role) {
        return $arr[$role];
    }
    return $arr;
}

/**
 * @param null $role
 * @return array|mixed
 */
function getAdminSetRoles($role = null)
{
    $arr = [
        'account' => 'Account Officer',
        'proprietor' => 'Proprietor',
        'admin' => 'Administrator',
        'principal' => 'Principal',
        'head_primary' => 'Head of Primary Schools',
        'hod_sec' => 'H.O.D Secondary',
        'hod_pri' => 'H.O.D Primary',
        'teacher' => 'Teacher',
        'librarian' => 'Librarian',


        /* 'instructor' => 'Instructor',
        'vice principal' => 'Vice Principal',
        'deputy head' => 'Deputy Head'*/
    ];

    if ($role) {
        return $arr[$role];
    }
    return $arr;
}

/**
 * @param null $type
 * @return array|mixed
 */
function getJobType($type = null)
{
    $arr = [
        'full time' => 'Full Time',
        'part time' => 'Part Time'
    ];

    if ($type) {
        return $arr[$type];
    }
    return $arr;
}

/**
 * Delete form
 * @param $params
 * @param string $label
 * @return string
 */
function deleteForm($params, $label = 'Delete')
{
    $html = Form::open(['method' => 'DELETE', 'route' => $params, 'class' => 'delete-form']);
    $html .= Form::submit($label, ['class' => 'btn btn-danger btn-delete']);
    $html .= Form::close();
    return $html;
}

/**
 * School Type
 * @return array
 **/
function schoolType()
{
    return array('british' => 'British', 'nigerian' => 'Nigerian', 'others' => 'Others');
}

function countries()
{
    return  array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
}

function defaultPasswordStatus()
{
    return 'default';
}

function todayDateTime()
{
    return date('Y-m-d H:i:s', strtotime('now'));
}

function todayDate()
{
    return date('Y-m-d', strtotime('now'));
}

function getDateFormat($dateTime)
{
    return date('Y-m-d', strtotime($dateTime));
}

function getDateFormatWords($dateTime)
{
    return date('l M d, Y', strtotime($dateTime));
}

function fromDate()
{
    return date('Y-m-d' . ' 07:30:00', time());
}

function toDate()
{
    return date('Y-m-d' . ' 16:00:00', time());
}
function convertPercentToUnitScore($factor, $numerator, $denominator = 100)
{
    $converted_score = $numerator / $denominator * $factor;
    return sprintf("%01.1f", $converted_score);
}
function deleteSingleElementFromString($parent_string, $child_string)
{
    $string_array = explode('~', $parent_string);

    $count_array = count($string_array);

    for ($i = 0; $i < ($count_array); $i++) {

        if ($string_array[$i] == $child_string) {

            unset($string_array[$i]);
        }
    }
    return $new_parent_str = implode('~', array_unique($string_array));
}
function addSingleElementToString($parent_string, $child_string)
{
    if ($parent_string == '') {
        $str =  $child_string;
    } else {
        $str =  $parent_string . '~' . $child_string;
    }


    $string_array = array_unique(explode('~', $str));

    return $new_parent_str = implode('~', $string_array);
}

/**
 * function to save photo path
 * @param String $school details (in json form)
 * @param Array $path array keys (type, file) with their file extension
 * @return String path
 **/

function photoPath($school, array $path = [])
{
    if ($path['type'] == 'default') {
        $file = $path['file'];
        return 'photo/' . $file;
    }
    $file = $path['file'];
    return 'schools/' . $school->folder_key . '/' . 'photo/' . $file;
}

function alternateClassName($name)
{
    if ($name == 'J.S.S') {
        return 'Junior Secondary';
    }
    if ($name == 'S.S.S') {
        return 'Senior Secondary';
    }

    return $name;
}
function scoreOptions($type = 'ca')
{
    $options = ['' => 'Select'];

    if ($type == 'exam') {
        for ($i = 70; $i <= 1; $i--) :
            $options[$i] = $i;
        endfor;
        return $options;
    }
    for ($i = 10; $i <= 1; $i--) :
        $options[$i] = $i;
    endfor;
    return $options;
}

function resultActions()
{
    return $result_action = array(
        'half' => 'nil',
        'full' => 'nil'
    );
}

function terms($term = null)
{
    $arr = [
        '1' => 'First',
        '2' => 'Second',
        '3' => 'Third'

    ];

    if ($term) {
        return $arr[$term];
    }
    return $arr;
}

function subjectTeacherSelectWithClassLevel($details)
{
    $subject_details = [];
    foreach ($details as $detail) :
        if ($detail->subject) {
            $subject_details[$detail->id] = $detail->subject->name . ' for ' . $detail->class->name . ' (' . formatLevel($detail->level->level) . ')';
        }
    endforeach;
    return $subject_details;
}

function randomColorCode()
{
    $tokens = 'ABC0123456789'; //'ABCDEF0123456789';
    $serial = '';
    for ($i = 0; $i < 6; $i++) {
        $serial .= $tokens[mt_rand(0, strlen($tokens) - 1)];
    }
    return '#' . $serial;
}

function randomcode()
{
    $tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789';
    $serial = '';
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 4; $j++) {
            $serial .= $tokens[mt_rand(0, strlen($tokens) - 1)];
        }
        if ($i < 2) {
            $serial .= '-';
        }
    }
    return $serial;
}

function schoolDays($day = null)
{
    $arr = [
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday'

    ];

    if ($day) {
        return $arr[$day];
    }
    return $arr;
}

function schoolDaysStr($day = null)
{
    $arr = [
        'Monday'    => '1',
        'Tuesday'   => '2',
        'Wednesday' => '3',
        'Thursday'  => '4',
        'Friday'    => '5'

    ];

    if ($day) {
        return $arr[$day];
    }
    return $arr;
}

/**
 *This ranks student based on score
 *@param $score = the score you want to rank
 *@param $scores = array of sorted scores from top to least
 *@return $position = position of the score
 */
function rankResult($score, $scores)
{
    rsort($scores);
    $position = "";
    foreach ($scores as $key => $each_score) {
        //$position = "";
        if ($score == $each_score) {
            $position = array_search($score, $scores) + 1;
            break;
        }
    }
    if ($position == '1' || (strlen($position) == '2' && substr($position, 0, 1) != '1') && substr($position, 1) == '1') {
        $position = $position . 'st';
    } else if ($position == '2' || (strlen($position) == '2' && substr($position, 0, 1) != '1') && substr($position, 1) == '2') {
        $position = $position . 'nd';
    } else if ($position == '3' || (strlen($position) == '2' && substr($position, 0, 1) != '1') && substr($position, 1) == '3') {
        $position = $position . 'rd';
    } else {
        $position = $position . 'th';
    }
    return $position;
}

function nationalExpectationDescription()
{
    $arr = [
        'WT' => 'Working Towards National Expectation',
        'WE' => 'Working as Expected',
        'WA' => 'Working Above Expectation',
    ];
    return $arr;
}

function nationalExpectationKey()
{
    $arr = [
        '0' => ['Score %', '0 - 49', '50 - 84', '85 - 100'],
        '1' => ['National Expectation', 'WT', 'WE', 'WA'],
        '2' => ['Grade', 'C', 'B', 'A'],
        '3' => ['Attainment', 'Developing', 'Secure', 'Outstanding'],
    ];
    return $arr;
}

function nationalExpectationGrade($grade)
{
    if ($grade >= 85 && $grade <= 100) {
        $attainment = ['A', 'Outstanding', 'WA'];
    } else if ($grade >= 50 && $grade <= 84) {
        $attainment = ['B', 'Secure', 'WE'];
    } else {
        $attainment = ['C', 'Developing', 'WT'];
    }

    return $attainment;
}
function registrationPinType($type = null)
{
    $arr = [
        'teacher'   => 'Teacher',
        'student' => 'Student',


    ];

    if ($type) {
        return $arr[$type];
    }
    return $arr;
}
function disabilities()
{

    return [
        "NA" => "Not Applicable",
        "Eye Defect" => "Eye Defect",
        "Ear Defect" => "Ear Defect",
        "Dumb" => "Dumb",
        "Paralyzed" => "Paralyzed"


    ];
}
function gender($gender = null)
{
    $arr = [
        'Male'   => 'Male',
        'Female' => 'Female',


    ];

    if ($gender) {
        return $arr[$gender];
    }
    return $arr;
}

function sections($name = null)
{
    $arr = [
        'A'   => 'A',
        'B'   => 'B',
        'C'   => 'C',
        'D'   => 'D',
        'E'   => 'E',
        'F'   => 'F'



    ];

    if ($name) {
        return $arr[$name];
    }
    return $arr;
}

function occupation()
{
    $arr = [
        'Civil Servant'   => 'Civil Servant',
        'Public Servant'   => 'Public Servant',
        'Professional'   => [
            'Architect' => 'Architect',
            'Banker' => 'Banker',
            'Doctor' => 'Doctor',
            'Engineer' => 'Engineer',
            'Nurse' => 'Nurse',
            'Lawyer' => 'Lawyer',
            'Teacher' => 'Teacher'
        ],
        'Business Person'   => 'Business Person',
        'Others'   => 'Others (if not listed here)',


    ];

    return $arr;
}

function hashing($string)
{
    $hash = hash('sha512', $string);
    return $hash;
}

function formatUniqNo($no)
{
    $no = $no * 1;
    if ($no < 10) {
        return '000' . $no;
    } else if ($no >= 10 && $no < 100) {
        return '00' . $no;
    } else if ($no >= 100 && $no < 1000) {
        return '0' . $no;
    } else {
        return $no;
    }
}
function mainDomainPublicPath($folder = null)
{
    return "https://school-point.com/" . $folder;
}
function subdomainPublicPath($folder = null)
{
    return "/home/schoolpoint/public_html/storage/" . $folder;
}

function portalPulicPath($folder = null)
{
    return "/home/schoolpoint/public_html/api/storage/" . $folder;
}

function folderSize($dir)
{
    $size = 0;

    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
        $size += is_file($each) ? filesize($each) : folderSize($each);
    }

    // this size is in Byte
    // we want to convert it to GB
    // 1Gb = 1024 ^ 3 Bytes OR 1Gb = 2 ^ 30

    return $size;
    // return sizeFilter($size); //byteToGB($size);
}

function byteToGB($byte)
{
    $gb =  $byte / 1024 / 1024 / 1024;
    return $gb;
}

function percentageDirUsage($dir_size, $total_usable)
{
    $used =  $dir_size / $total_usable * 100;
    return (float) sprintf('%01.2f', $used);
}
function folderSizeFilter($bytes)
{
    $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

    for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);

    return (round($bytes, 2) . $label[$i]);
}
<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block displaying information about current logged-in user.
 *
 * This block can be used as anti cheating measure, you
 * can easily check the logged-in user matches the person
 * operating the computer.
 *
 * @package    block_learnerprofile
 * @copyright  2010 Remote-Learner.net
 * @author     Ufuoma Apoki <ufuomaapoki@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Displays the current user's profile information.
 *
 * @copyright  2010 Remote-Learner.net
 * @author     Olav Jordan <olav.jordan@remote-learner.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_learnerprofile extends block_base {
    /**
     * block initializations
     */
    public function init() {
        $this->title   = get_string('pluginname', 'block_learnerprofile');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real users
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;

        if (!isset($this->config->display_picture) || $this->config->display_picture == 1) {
            $this->content->text .= '<div class="learnerprofileitem picture">';
            $this->content->text .= $OUTPUT->user_picture($USER, array('courseid'=>$course->id, 'size'=>'100', 'class'=>'profilepicture'));  // The new class makes CSS easier
            $this->content->text .= '</div>';
        }

        $this->content->text .= '<div class="learnerprofileitem fullname">'.fullname($USER).'</div>';

        //global $DB;
        //$course = $DB->get_record('course', array('id' => 3));
        //$info = get_fast_modinfo($course);
        //print_object($info);
        
        //Get responses for the questionnaire for logged-in user
        $user_resp = $DB->get_records('questionnaire_response', array('userid' => $USER->id));
        $user_resp = array_shift($user_resp);
        
        if (!empty($user_resp)){
            $responses = $DB->get_records('questionnaire_resp_single', array('response_id' => $user_resp->id));
            $responses = array_values($responses);
            
            //Get questions for active/reflective dimension
            $active_reflective = array($responses[0]->choice_id, $responses[4]->choice_id, $responses[8]->choice_id,$responses[12]->choice_id,$responses[16]->choice_id, $responses[20]->choice_id, 
                                    $responses[24]->choice_id, $responses[28]->choice_id, $responses[32]->choice_id, $responses[36]->choice_id, $responses[40]->choice_id);

            $reflective = 0;
            for ($i = 0; $i < 11; $i++) {
                if($active_reflective[$i] % 2 == 0)   
                    $reflective++;
            }

            if ($reflective == 5 || $reflective== 6 || $reflective == 4 || $reflective == 7) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Active/Reflective: Balanced".'</div>';
            } elseif ($reflective == 8 || $reflective == 9) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Active/Reflective: Moderately Reflective".'</div>';
            } elseif ($reflective == 10 || $reflective == 11) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Active/Reflective: Strongly Reflective".'</div>';
            }elseif ($reflective == 2 || $reflective == 3) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Active/Reflective: Moderately Active".'</div>';
            }elseif ($reflective == 0 || $reflective == 1) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Active/Reflective: Strongly Active".'</div>';
            }
            
            //Get questions for sensing/intuitive dimension
            $sensing_intuitive = array($responses[1]->choice_id, $responses[5]->choice_id, $responses[9]->choice_id,$responses[13]->choice_id,$responses[17]->choice_id, $responses[21]->choice_id, 
                                    $responses[25]->choice_id, $responses[29]->choice_id, $responses[33]->choice_id, $responses[37]->choice_id, $responses[41]->choice_id);
                    $intuitive = 0;
            for ($i = 0; $i < 11; $i++) {
                if($sensing_intuitive[$i] % 2 == 0)   
                    $intuitive++;
            }

            if ($intuitive == 5 || $intuitive== 6 || $intuitive == 4 || $intuitive == 7) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sensing/Intuitive: Balanced".'</div>';
            } elseif ($intuitive == 8 || $intuitive == 9) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sensing/Intuitive: Moderately Intuitive".'</div>';
            } elseif ($intuitive == 10 || $intuitive == 11) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sensing/Intuitive: Strongly Intuitive".'</div>';
            }elseif ($intuitive == 2 || $intuitive == 3) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sensing/Intuitive: Moderately Sensing".'</div>';
            }elseif ($intuitive == 0 || $intuitive == 1) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sensing/Intuitive: Strongly Sensing".'</div>';
            }

            //Get questions for visual/verbal dimension
            if (!empty($responses)){
            $visual_verbal = array($responses[2]->choice_id, $responses[6]->choice_id, $responses[10]->choice_id,$responses[14]->choice_id,$responses[18]->choice_id, $responses[22]->choice_id, 
                                        $responses[26]->choice_id, $responses[30]->choice_id, $responses[34]->choice_id, $responses[38]->choice_id, $responses[42]->choice_id);
            }
            $verbal = 0;
            for ($i = 0; $i < 11; $i++) {
                if($visual_verbal[$i] % 2 == 0)   
                    $verbal++;
            }

            if ($verbal == 5 || $verbal== 6 || $verbal == 4 || $verbal == 7) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Visual/Verbal: Balanced".'</div>';
            } elseif ($verbal == 8 || $verbal == 9) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Visual/Verbal: Moderately Verbal".'</div>';
            } elseif ($verbal == 10 || $verbal == 11) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Visual/Verbal: Strongly Verbal".'</div>';
            }elseif ($verbal == 2 || $verbal == 3) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Visual/Verbal: Moderately Visual".'</div>';
            }elseif ($verbal == 0 || $verbal == 1) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Visual/Verbal: Strongly Visual".'</div>';
            }

            //Get questions for sequential/global dimension
            if (!empty($responses)){
            $sequential_global = array($responses[3]->choice_id, $responses[7]->choice_id, $responses[11]->choice_id,$responses[15]->choice_id,$responses[19]->choice_id, $responses[23]->choice_id, 
                                        $responses[27]->choice_id, $responses[31]->choice_id, $responses[35]->choice_id, $responses[39]->choice_id, $responses[43]->choice_id);
            }
            $global = 0;
            for ($i = 0; $i < 11; $i++) {
                if($sequential_global[$i] % 2 == 0)   
                    $global++;
            }

            if ($global == 5 || $global== 6 || $global == 4 || $global == 7) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sequential/Global: Balanced".'</div>';
            } elseif ($global == 8 || $global == 9) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sequential/Global: Moderately Global".'</div>';
            } elseif ($global == 10 || $global == 11) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sequential/Global: Strongly Global".'</div>';
            }elseif ($global == 2 || $global == 3) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sequential/Global: Moderately Sequential".'</div>';
            }elseif ($global == 0 || $global == 1) {
                $this->content->text .= '<div class="learnerprofileitem learningstyle">'."Sequential/Global: Strongly Sequential".'</div>';
            }                        
        }

        if(!isset($this->config->display_country) || $this->config->display_country == 1) {
            $countries = get_string_manager()->get_list_of_countries(true);
            if (isset($countries[$USER->country])) {
                $this->content->text .= '<div class="learnerprofileitem country">';
                $this->content->text .= get_string('country') . ': ' . $countries[$USER->country];
                $this->content->text .= '</div>';
            }
        }

        if(!isset($this->config->display_city) || $this->config->display_city == 1) {
            $this->content->text .= '<div class="learnerprofileitem city">';
            $this->content->text .= get_string('city') . ': ' . format_string($USER->city);
            $this->content->text .= '</div>';
        }

        if(!isset($this->config->display_email) || $this->config->display_email == 1) {
            $this->content->text .= '<div class="learnerprofileitem email">';
            $this->content->text .= obfuscate_mailto($USER->email, '');
            $this->content->text .= '</div>';
        }

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * allow more than one instance of the block on a page
     *
     * @return boolean
     */
    public function instance_allow_multiple() {
        //allow more than one instance on a page
        return false;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    function instance_allow_config() {
        //allow instances to have their own configuration
        return false;
    }

    /**
     * instance specialisations (must have instance allow config true)
     *
     */
    public function specialization() {
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all'=>true);
    }

    /**
     * post install configurations
     *
     */
    public function after_install() {
    }

    /**
     * post delete configurations
     *
     */
    public function before_delete() {
    }

}

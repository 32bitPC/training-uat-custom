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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require($CFG->dirroot.'/cohort/lib.php');
require($CFG->dirroot.'/phlcohort/lib.php');
require_once($CFG->libdir.'/adminlib.php');

require($CFG->dirroot.'/phlcohort/edit_form.php');
require_once($CFG->dirroot . '/lib/coursecatlib.php');




$cohortid=optional_param('id',0, PARAM_INT);
$searchquery=optional_param('userinfo','', PARAM_TEXT); 
$confirm= optional_param('confirm',0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$showall = optional_param('showall', false, PARAM_BOOL);
$perpage=100;

$contextid=0;

require_login();

$SESSION->bulk_users = array();
$params = array('id'=>$cohortid,'page' => $page,'userinfo'=>$searchquery);
if ($showall) {
    $params['showall'] = true;
}
$baseurl = new moodle_url('/phlcohort/user.php', $params);



if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
} else {
    $context = context_system::instance();
}

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

$category = null;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id'=>$context->instanceid), '*', MUST_EXIST);
}

$manager = has_capability('moodle/cohort:manage', $context);
$canassign = has_capability('moodle/cohort:assign', $context);
if (!$manager) {
    ;//require_capability('moodle/cohort:view', $context);
}

$strcohorts = get_string('cohorts', 'cohort');


if ($category) {
    $PAGE->set_pagelayout('admin');
    $PAGE->set_context($context);
    $PAGE->set_url('/phlcohort/index.php', array('contextid'=>$context->id));
    $PAGE->set_title($strcohorts);
    $PAGE->set_heading($COURSE->fullname);
    $showall = false;
} else {
    admin_externalpage_setup('cohorts', '', null, '', array('pagelayout'=>'report'));
}


$PAGE->set_context($context);
$PAGE->set_url('/phlcohort/index.php', array('contextid'=>$context->id));

echo $OUTPUT->header();

//echo $confirm."XXX";
if($confirm>0)
{    
    
    $selectedCohorts=optional_param_array('chon',null,PARAM_INT);
    
        
        //var_dump($SESSION->bulk_users_phl_attend);
        foreach ($SESSION->bulk_users_phl_attend as $cmid) {            

            $attend=false;
            if($selectedCohorts!=null)
            {
                foreach ($selectedCohorts as $selectedcmid) {                     
                    
                    if($selectedcmid==$cmid)
                        $attend=true;                

                }
            }

            if($attend)
            {
                //echo $cmid."XXX";
                if(!cohort_is_member_attended($cmid))                   
                    cohort_add_member_attended($cmid);                       
            }
            else
            {
                //echo $cmid."YYY";
                if(cohort_is_member_attended($cmid))
                        cohort_remove_member_attended($cmid);   
            }

        }
        //echo $cohortid."XXX";
}

                    

$cohorts = cohort_get_phl_cohort_detail($cohortid);


 


foreach ($cohorts as $cohort) {    

    $search='';
    $search .= html_writer::start_div('search',array('style' =>"font-size:15px;!important"));
    $search .= html_writer::start_div('row',array('style' =>"font-size:17px;color:#f60;margin-bottom: 20px;"));

    $search .= html_writer::start_div('col-md-1');
    $search .= "Mã Lớp:";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-2',array('style'=>"font-weight: bold;"));
    $search .= $cohort->idnumber;
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-1');
    $search .= "Tên Lớp:";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-6',array('style'=>"font-weight: bold;"));
    $search .= $cohort->name;
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-1');
    $search .= html_writer::link("./assign.php?id=$cohort->id&returnurl=%2Fphlcohort%2Fuser.php%3Fid%3D$cohort->id","Thêm/Xóa Học Viên",array('class' =>'btn btn-primary'));
    $search .= html_writer::end_div();

    $search .= html_writer::end_div();    
     $search .= html_writer::start_div('row',array('style' =>"margin-top:20px;"));

    $search .= html_writer::start_div('col-md-1');
    $search .= "Khóa học:";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-2',array('style'=>"font-weight: bold;"));
    $search .= $cohort->fullname;
    $search .= html_writer::end_div();
    
    $search .= html_writer::start_div('col-md-1');
    $search .= "CVHL:";
    $search .= html_writer::end_div();
    
    $search .= html_writer::start_div('col-md-8',array('style'=>"font-weight: bold;"));
    $search .= $cohort->trainer;
    $search .= html_writer::end_div();

    $search .= html_writer::end_div();
    $search .= html_writer::start_div('row',array('style' =>"margin-top:20px;"));

    $search .= html_writer::start_div('col-md-1');
    $search .= "Ngày Học:";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-2',array('style'=>"font-weight: bold;"));
    $search .=  date("d/m/Y",$cohort->ngayhoc);;
    $search .= html_writer::end_div();

    /*
    $search .= html_writer::start_div('col-md-1');
    $search .= "Ngày Thi";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-2');
    $search .= $cohort->ngaythi;
    $search .= html_writer::end_div();
    */

    $search .= html_writer::start_div('col-md-1');
    $search .= "Khu Vực:";
    $search .= html_writer::end_div();

    $search .= html_writer::start_div('col-md-8',array('style'=>"font-weight: bold;"));
    $search .= $cohort->tenmien . " - " . $cohort->tenkhuvuc;
    $search .= html_writer::end_div();

    $search .= html_writer::end_div();
    /*
    $search .= html_writer::start_div('row');
    $search .= html_writer::start_div('col-md-1');
    $search .= "Miêu tả:";
    $search .= html_writer::end_div();
    $search .= html_writer::start_div('col-md-11');
    $search .= $cohort->description;
    $search .= html_writer::end_div();
    $search .= html_writer::end_div();
    */
  

    echo $search;

}

    

    $search  = html_writer::start_tag('form', array('id'=>'searchcohortquery', 'method'=>'post', 'class' => 'form-inline search-cohort'));    

    $search .= html_writer::start_div('row');    
    $search .= html_writer::start_div('col-md-7');
    $search .= '<h3 style="font-weight: 600;text-transform: uppercase;margin-top:30px;font-size:13px;" id="instance-351-header" class="card-title">Danh Sách Học Viên Đã Đăng Ký</h3>';
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-2',array('style' =>"text-align:right;"));
    $search .= "Tìm Học Viên";
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-2');
    $search .= html_writer::empty_tag('input', array('type' => 'text', 'value' => $searchquery,'name'=>"userinfo"));    
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-1');    
    $search .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => "Tìm",
            'class' => 'btn btn-secondary'));
    $search .= html_writer::end_div();   
    $search .= html_writer::end_div();
    $search .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'confirm', 'value'=>'0'));
    $search .= html_writer::end_tag('form');
    echo $search;



    $users= cohort_get_all_users($page,$perpage,$searchquery,$cohortid);

 

    $data = array();
    $editcolumnisempty = true;
    $showall=true;
    $i=0;
    $SESSION->bulk_users_phl=array();
    $SESSION->bulk_users_phl_attend=array();
    foreach($users['users'] as $user) {

        if (!isset($SESSION->bulk_users_phl[$user->id])) {
            $SESSION->bulk_users_phl[$user->id] = $user->id;

        }
        if (!isset($SESSION->bulk_users_phl_attend[$user->cmid])) {
            $SESSION->bulk_users_phl_attend[$user->cmid] = $user->cmid;

        }

        $i++;        
        $line = array();    
        $line[] = $i + $page*$perpage;    
        $line[] = $user->firstname." ".$user->lastname;            
        $line[] = $user->username;    
        $line[] = $user->email;    
        $line[] = $user->phone1;            
        //$line[] = date("d/m/Y",$user->ngaydangky);         
        if(!cohort_is_member_attended($user->cmid))   
        {
            //$line[] = "Không";
            $line[] = '<input type="checkbox" class="form-control " name="chon[]"  value="'.$user->cmid.'" >';
        }
        else
        {
            //$line[] = "Có";
            $line[] = '<input type="checkbox" class="form-control " checked="checked"  name="chon[]"  value="'.$user->cmid.'" >';
        }
        
        
        $line[] = '';
        //$line[] = $DB->count_records('cohort_members', array('cohortid'=>$cohort->id));    
        //$line[] = '<input type="text" class="form-control " name="name" id="id_name" value="" size="50" maxlength="254">';
        
        

        $data[] = $row = new html_table_row($line);
        if (!$cohort->visible) {
            $row->attributes['class'] = 'dimmed_text';
        }
    }

    $table = new html_table();
    $table->head  = array("STT","Họ Tên","CMTND","Email","Điện Thoại","<input type=\"checkbox\" class=\"form-control \" name=\"chon[]\" onclick=\"$('input:checkbox').prop('checked', this.checked);\" style=\"    transform: scale(1.2);\" >&nbsp;Tham Dự");
    //$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size','leftalign name','leftalign name');


    foreach ($data as $row) {
       array_pop($row->cells);
    }

    $table->id = 'cohorts';
    $table->attributes['class'] = 'admintable generaltable';
    $table->data  = $data;

    echo $OUTPUT->paging_bar($users['totalusers'], $page, $perpage, $baseurl);

    $search  = html_writer::start_tag('form', array('id'=>'searchcohortquery', 'method'=>'post', 'class' => 'form-inline search-cohort'));

    $search.= html_writer::table($table);
    $search .= html_writer::start_div('row');    
    $search .= html_writer::start_div('col-md-2');
    $search .= html_writer::link("./manager.php","Danh Sách Lớp",array('class' =>'btn btn-primary'));
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-1');    
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-2');    

    //if(isset($cohort->trainer))
    if(false)
        $search .= html_writer::link("./excel_reader/PHPExcel/download.php?context=members&id=$cohortid&trainer=$cohort->trainer","Download Danh Sách",array('class' =>'btn btn-primary'));    
    else
        $search .= html_writer::link("./download.php?id=$cohortid","Download Danh Sách",array('class' =>'btn btn-primary'));    
    $search .= html_writer::end_div();        
    $search .= html_writer::start_div('col-md-5');
//search .= html_writer::empty_tag('input', array('onclick'=>"document.getElementById('confirm').value=2;return true;", 'type' => 'submit', 'value' => "Xác nhận Học viên đã chọn CÓ tham dự.",'class' => 'btn btn-secondary'));
    $search .= html_writer::end_div();    
    $search .= html_writer::start_div('col-md-2');
    $search .= html_writer::empty_tag('input', array('onclick'=>"document.getElementById('confirm').value=1;return true;", 'type' => 'submit', 'value' => "Xác nhận",
            'class' => 'btn btn-secondary'));
    $search .= html_writer::end_div();    

    $search .= html_writer::end_div();
    $search .= html_writer::empty_tag('input', array('type'=>'hidden','id'=>'confirm','name'=>'confirm', 'value'=>'0'));
    $search .= html_writer::end_tag('form');
    
 echo $search;
echo $OUTPUT->footer();


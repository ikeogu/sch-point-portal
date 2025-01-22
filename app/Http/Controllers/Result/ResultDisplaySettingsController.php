<?php

namespace App\Http\Controllers\Result;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ResultDisplaySetting;
use Illuminate\Support\Facades\Storage;

class ResultDisplaySettingsController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $curriculum_level_group_id = $request->curriculum_level_group_id;
        $result_setting = $this->getResultSettings($curriculum_level_group_id); // ResultDisplaySetting::where('school_id', $school_id)->get();
        return response()->json(compact('result_setting'), 200);
    }

    public function update(Request $request)
    {
        // return $request;
        $result_setting = ResultDisplaySetting::find($request->id);
        $school = $this->getSchool();
        // $curriculum_level_group_id = $request->curriculum_level_group_id;
        // $result_setting = ResultDisplaySetting::where(['school_id' => $school_id, 'curriculum_level_group_id' => $curriculum_level_group_id])->first();
        $display_exam_score_only_for_full_term = $request->display_exam_score_only_for_full_term;

        $exam = $request->exam;
        // if ($display_exam_score_only_for_full_term === 'yes') {
        //     $exam = 100;
        // }
        $result_setting->no_of_ca = $request->no_of_ca;
        $result_setting->ca1 = ($request->ca1 !== 'null') ? $request->ca1 : NULL;
        $result_setting->ca2 = ($request->ca2 !== 'null') ? $request->ca2 : NULL;
        $result_setting->ca3 = ($request->ca3 !== 'null') ? $request->ca3 : NULL;
        $result_setting->ca4 = ($request->ca4 !== 'null') ? $request->ca4 : NULL;
        $result_setting->ca5 = ($request->ca5 !== 'null') ? $request->ca5 : NULL;
        $result_setting->exam = $exam;
        $result_setting->no_of_ca_for_midterm = $request->no_of_ca_for_midterm;
        $result_setting->add_midterm_score_to_full_result = $request->add_midterm_score_to_full_result;
        $result_setting->midterm_score_limit = $request->midterm_score_limit;
        $result_setting->add_attendance_to_ca = $request->add_attendance_to_ca;
        $result_setting->attendance_score_limit = $request->attendance_score_limit;

        $result_setting->display_exam_score_only_for_full_term = $display_exam_score_only_for_full_term;
        $result_setting->display_student_position = $request->display_student_position;
        $result_setting->display_student_subject_position = $request->display_student_subject_position;
        $result_setting->display_highest_score = $request->display_highest_score;
        $result_setting->display_lowest_score = $request->display_lowest_score;
        $result_setting->display_class_average_score = $request->display_class_average_score;
        $result_setting->display_student_subject_average = $request->display_student_subject_average;
        $result_setting->display_student_class_average = $request->display_student_class_average;
        $result_setting->display_grade = $request->display_grade;
        $result_setting->display_student_behovior_and_skill_rating = $request->display_student_behovior_and_skill_rating;
        $result_setting->display_logo_for_result_background = $request->display_logo_for_result_background;
        $result_setting->display_school_name_on_result = $request->display_school_name_on_result;
        $result_setting->display_school_address_on_result = $request->display_school_address_on_result;

        if ($request->use_school_logo === 'yes') {
            $result_setting->logo = $school->logo;
        }


        $result_setting->save();
        if ($request->file('result_logo') != null && $request->file('result_logo')->isValid()) {

            $this->updateLogo($request, $result_setting);
        }
        return response()->json(compact('result_setting'), 200);
    }

    private function updateLogo(Request $request, $result_setting)
    {
        $school = $this->getSchool();
        if ($request->file('result_logo') != null && $request->file('result_logo')->isValid()) {


            $mime = $request->file('result_logo')->getClientMimeType();

            if ($mime == 'image/png' || $mime == 'image/jpeg' || $mime == 'image/jpg' || $mime == 'image/gif') {
                // delete older ones
                // if (Storage::disk('public')->exists($result_setting->logo)) {
                //     Storage::disk('public')->delete($result_setting->logo);
                // }
                $name = "result_logo_" . $result_setting->id . '_' . time() . "." . $request->file('result_logo')->guessClientExtension();
                $folder_key = $school->folder_key;
                $folder = "schools/" . $folder_key;
                $logo = $request->file('result_logo')->storeAs($folder, $name, 'public');

                $result_setting->logo = $logo;

                if ($result_setting->save()) {
                    return $result_setting->logo;
                }
            }
        }
    }
}

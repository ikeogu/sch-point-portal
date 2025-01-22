<?php

namespace Modules\Core\Http\Controllers;

use App\Models\ClassActivity;
use App\Models\Guardian;
use App\Models\Medical;
use App\Models\Remark;
use App\Models\Routine;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentsInClass;
use App\Models\ClassTeacher;
use App\Models\TempFile;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Staff;
use App\Models\ClassAttendance;
use App\Models\AuditTrail;
use App\Models\News;
use App\Models\Event;
use App\Models\PartnerSchool;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    private $range = 10;

    public static function routes()
    {
        Route::get('/profile', 'ProfileController@index')->name('profile');
        Route::post('/mediaUpload', 'ProfileController@updateFile')->name('mediaUpload');
        Route::post('/postNewEvent', 'ProfileController@postEvent')->name('postNewEvent');
        Route::post('/cancelPost', 'ProfileController@cancelPost')->name('cancelPost');
        Route::get('/timelines/{post}', 'ProfileController@timeline')->name('timeline');
        Route::get('/timelines/valid/{count}', 'ProfileController@valid')->name('valid');
        Route::get('/timeline/first', 'ProfileController@first')->name('first');
    }

    public function index()
    {
        $school_id = $this->getSchool()->id;
        $student = $this->getStudent();
        $user = $this->getUser();
        $timelines = $this->getTimeLine();
        //$activities = $this->getActivities();
        //        $times = $this->getTimeLineDay();
        //        $timelines = $timelines->slice( 0, 1 );
        //
        $data = compact('user', 'student', 'timelines');
        //        $data = compact('user', 'student', 'timelines' );
        return $this->render('core::profile.student', $data);
    }

    private function getTimeLine()
    {
        $school_id = $this->getSchool()->id;
        return Timeline::where('school_id', $school_id)->latest()->get();
    }

    //    private function getTimeLine()
    //    {
    //        return Timeline::selectRaw('id, school_id, user_id, content, day(created_at) day, month(created_at) month,
    //        year( created_at ) year ' )->get();
    //    }

    private function getTimeLineDay()
    {
        $school_id = $this->getSchool()->id;
        return Timeline::selectRaw('day(created_at) day, month(created_at) month,
        year( created_at ) year, created_at')->groupBy('year', 'month', 'day')->orderByRaw('min(created_at) desc')->where('school_id', $school_id)->get();
    }

    public function updateFile(Request $request)
    {
        return $this->storeMedia($request);
    }

    public function cancelPost(Request $request)
    {
        $this->clearAll();
    }

    public function storeMedia(Request $request)
    {
        $mediaFile = $request->file('uploadedFile');
        $type = $mediaFile->getMimeType();
        $file_link = $mediaFile->store('/temp', 'public');
        //        $file_link = $mediaFile->store('/temp' );
        //        $user_id = $this->getStudent()->user_id;
        //        TempFile::create( compact( 'user_id', 'file_link' ) );
        //        session()->push( 'medias', $file_link );
        session()->push('medias', compact("file_link", "type"));
        //        return $this->getThumbnail( $mediaFile, $file_link, $type );
        //        return $this->getThumbnail( $mediaFile, Storage::url( $file_link ), $type );
        return $this->getThumbnail($mediaFile, Storage::disk("public")->url($file_link), $type);
    }

    public function getThumbnail($mediaFile, $mediaPath, $type)
    {
        //        return $this->getImageTemplate( $mediaPath, $mediaFile->getClientOriginalName(), $mediaFile->getSize() );
        if (str_contains($type, 'image')) {
            return $this->getImageTemplate($mediaPath, $mediaFile->getClientOriginalName(), $mediaFile->getSize());
        }
        if (str_contains($type, 'video')) {
            return $this->getVideoTemplate($mediaPath, $mediaFile->getClientOriginalName(), $mediaFile->getSize());
        } else
            return "";
    }

    //    public function getThumbnail($mediaFile, $mediaPath )
    //    {
    //        if ( str_contains( $mediaFile->getMimeType(), 'image'))
    //        {
    //            return $this->getImageTemplate( $mediaPath, $mediaFile->getClientOriginalName(), $mediaFile->getSize() );
    //        }
    //        if ( str_contains( $mediaFile->getMimeType(), 'video'))
    //        {
    //            return $this->getVideoTemplate( $mediaPath, $mediaFile->getClientOriginalName(), $mediaFile->getSize() );        }
    //        else
    //            return "";
    //    }

    public function getImageTemplate($src, $name, $size)
    {
        return "
        <li id='attachee' style='width: 100px; height: 100px'>
            <img width=\"100\" height=\"100\" src=\"$src\" alt=\"Attachment\">
          </li>
          ";
    }

    //    public function getImageTemplate( $src, $name, $size )
    //    {
    //        return "
    //        <li id='attachee' style='width: 50px; height: 50px'>
    //        <span class=\"mailbox-attachment-icon has-img\">
    //        <img width=\"50\" height=\"50\" src=\"$src\" alt=\"Attachment\">
    //        </span>
    //        <div class=\"mailbox-attachment-info\">
    //            <h6 class=\"mailbox-attachment-name\" style='overflow: no-display'>$name</h6>
    //                <span class=\"mailbox-attachment-size\">
    //                  $size B
    //                </span>
    //          </div>
    //          </li>
    //          ";
    //    }

    public function getVideoTemplate($src, $name, $size)
    {
        return "
        <li id='attachee' style='width: 100px; height: 100px'>
            <video width=\"100\" height=\"100\" alt=\"Attachment\">
            <source src=\"$src\" type=\"video/mp4\">
            Your browser does not support the video tag.
            </video>
          </li>
          ";
    }

    //    public function getVideoTemplate( $src, $name, $size )
    //    {
    //        return "
    //        <li id='attachee' >
    //        <span class=\"mailbox-attachment-icon has-img\">
    //            <video width=\"50\" height=\"50\" alt=\"Attachment\">
    //            <source src=\"$src\" type=\"video/mp4\">
    //            Your browser does not support the video tag.
    //            </video>
    //        </span>
    //        <div class=\"mailbox-attachment-info\">
    //            <h6 class=\"mailbox-attachment-name\" style='overflow: no-display'>$name</h6>
    //                <span class=\"mailbox-attachment-size\">
    //                  $size B
    //                </span>
    //          </div>
    //          </li>
    //          ";
    //    }

    public function clearAll()
    {
        //        $user_id = $this->getStudent()->user_id;
        //        $medias = TempFile::where( 'user_id', '=', $user_id )->pluck('file_link');
        if (session()->has('medias')) {
            $medias = session()->remove('medias');
            foreach ($medias as $media) {
                Storage::delete($media);
            }
        }
        //        TempFile::where( 'user_id', '=', $user_id )->delete();
    }

    public function clearSession()
    {
        //        TempFile::where( 'user_id', '=', $this->getStudent()->user_id )->delete();
        session()->forget('medias');
    }

    public function viewMedia()
    {
    }

    public function postEvent(Request $request)
    {
        //$student = $this->getStudent();
        if (request('content') == "")
            return "false";
        $timeline = Timeline::create([
            'school_id' => $this->getSchool()->id,
            'user_id' => $this->getUser()->id,
            'content' => request('content'),
            'status' => 1
        ]);
        //        $user_id = $this->getStudent()->user_id;
        //        $medias = TempFile::where( 'user_id', '=', $user_id )->pluck('file_link');
        $medias = session()->remove('medias');
        //        dd( $medias );
        if ($medias != null) {
            # code...
            foreach ($medias as $media) {
                $timeline->addTimelineMedia($media);
            }
        }

        //        $this->clearSession();
    }

    public function getActivities()
    {
        return ClassActivity::latest()->get();
    }

    public function timeline($index)
    {
        $timelines = $this->getTimeLine();
        $start = $index * $this->range;
        if ($start < $timelines->count()) {
            $size = $timelines->count() - $start;
            $size = $size < $this->range ? $size : $this->range;
            $last = ($start + $size) == $timelines->count();
            $timelines = $timelines->slice($start, $size);

            $data = compact('timelines', 'last');
            return view('profile.partials.timelines', $data);
        }
    }

    public function first()
    {
        $timelines = $this->getTimeLine();
        $timelines = $timelines->slice(0, 1);
        $last = false;
        $data = compact('timelines', 'last');
        return view('profile.partials.timelines', $data);
    }

    public function valid($count)
    {
        $school_id = $this->getSchool()->id;
        $result = ($count - 1) * $this->range < Timeline::where('school_id', $school_id)->count();
        return $result ? "true" : "false";
    }
}

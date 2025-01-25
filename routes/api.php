<?php

use App\Http\Controllers\Assignment\AssignmentsController;
use App\Http\Controllers\Attendance\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CurriculumCategoryController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\Library\LibrariesController;
use App\Http\Controllers\LMS\ClassroomsController;
use App\Http\Controllers\LMS\QuizController;
use App\Http\Controllers\Materials\CurriculaController;
use App\Http\Controllers\Materials\MaterialsController;
use App\Http\Controllers\Messages\MessagesController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Result\GradesController;
use App\Http\Controllers\Result\ResultDisplaySettingsController;
use App\Http\Controllers\Result\ResultsController;
use App\Http\Controllers\SchoolsController;
use App\Http\Controllers\Setup\ClassesController;
use App\Http\Controllers\Setup\EventsController;
use App\Http\Controllers\Setup\LevelsController;
use App\Http\Controllers\Setup\PermissionsController;
use App\Http\Controllers\Setup\RolesController;
use App\Http\Controllers\Setup\SectionsController;
use App\Http\Controllers\Setup\SessionsController;
use App\Http\Controllers\Setup\SubjectsController;
use App\Http\Controllers\Setup\TermsController;
use App\Http\Controllers\Setup\TimelinesController;
use App\Http\Controllers\TimeTable\RoutinesController;
use App\Http\Controllers\Users\GuardiansController;
use App\Http\Controllers\Users\PartnersController;
use App\Http\Controllers\Users\StudentsController;
use App\Http\Controllers\Users\UsersController;
use App\Http\Controllers\Users\StaffController;
use App\Http\Controllers\Account\FeesController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\PaymentsController;
use App\Http\Controllers\Account\SalaryController;
use App\Http\Controllers\Setup\RegistrationPinsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('materials/read/{material}', [MaterialsController::class, 'readMaterial']);
Route::get('fetch-curriculum-setup', [CurriculumCategoryController::class, 'fetchCurriculumCategory']);
Route::get('set-admin-role', [Controller::class, 'setAdminRole']);
Route::post('register-potential-school', [SchoolsController::class, 'registerPotentialSchool']);
Route::post('artisan', [Controller::class, 'artisanCommand']);
Route::post('update-school', [Controller::class, 'artisanCommand']);
Route::get('confirm-pin', [RegistrationPinsController::class, 'confirmPin']);
Route::get('students/create', [StudentsController::class, 'create']);
Route::get('staff/create', [StaffController::class, 'create']);
Route::post('students/store-with-pin', [StudentsController::class, 'storeWithPin']);
Route::post('staff/store-with-pin', [StaffController::class, 'storeWithPin']);
Route::get('fetch-necessary-params', [Controller::class, 'fetchNecessayParams']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('register', [AuthController::class, 'register'])->middleware('permission:create-users');

    Route::middleware(['auth:sanctum', 'log.sanctum.token'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('user', [AuthController::class, 'user']); //->middleware('permission:read-users');
    });
});


//////////////////////////////// APP APIS //////////////////////////////////////////////
Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix' => 'school'], function () {
        Route::get('create', [SchoolsController::class, 'create']);
        Route::post('register', [SchoolsController::class, 'registerPotentialSchool']);
    });
    // Protected routes for authenticated users
    Route::get('user-notifications', [UsersController::class, 'userNotifications']);
    Route::get('notification/mark-as-read', [UsersController::class, 'markNotificationAsRead']);

    // Access Control Roles & Permission
    Route::group(['prefix' => 'acl'], function () {
        Route::get('roles/index', [RolesController::class, 'index']);
        Route::post('roles/save', [RolesController::class, 'store']);
        Route::put('roles/update/{role}', [RolesController::class, 'update']);
        Route::post('roles/assign', [RolesController::class, 'assignRoles']);


        Route::get('permissions/index', [PermissionsController::class, 'index']);
        Route::post('permissions/assign-user', [PermissionsController::class, 'assignUserPermissions']);
        Route::post('permissions/assign-role', [PermissionsController::class, 'assignRolePermissions']);
    });

    //////////////////////ACCOUNT//////////////////////////
    Route::group(['prefix' => 'account'], function () {
        Route::group(['prefix' => 'fee-settings'], function () {
            Route::get('school-fee', [FeesController::class, 'schoolFeeSettings']);
            Route::get('fee-purposes', [FeesController::class, 'feePurposes']);

            Route::post('store/school-fee', [FeesController::class, 'storeFeeSettings']);
            Route::put('update/school-fee/{school_fee}', [FeesController::class, 'updateFeeSetting']);
            Route::put('change-status/{school_fee}', [FeesController::class, 'changeFeeStatus']);

            Route::get('others', [FeesController::class, 'otherFeeSettings']);
            Route::post('store/other-fees', [FeesController::class, 'storeOtherFeeSettings']);
            Route::put('update/other-fee/{other_fee}', [FeesController::class, 'updateOtherFeeSetting']);


            Route::post('apply/students-school-fee', [FeesController::class, 'applyStudentsSchoolFees']);

            Route::post('save-paystack-key', [FeesController::class, 'savePaystackKey']);

            Route::get('view-student-fee-details/{payment_monitor}', [FeesController::class, 'viewStudentFeeDetails']);
            Route::put('set-payable-nonpayable-fee/{payment_monitor}', [FeesController::class, 'setPayableAndNonPayableFee']);
        });
        Route::group(['prefix' => 'fee-payments'], function () {


            Route::get('monitor-table', [PaymentsController::class, 'paymentsMonitorTable']);
            Route::get('students-table', [PaymentsController::class, 'studentPaymentTable']);
            Route::get('unapproved', [PaymentsController::class, 'unapprovedPayments']);

            Route::post('pay-via-cash', [PaymentsController::class, 'payViaCash']);
            Route::post('pay-via-card', [PaymentsController::class, 'payViaCard']);


            Route::put('approve-school-fee/{school_fee_payment}', [PaymentsController::class, 'approveFeePayment']);
        });
        Route::group(['prefix' => 'salary'], function () {


            Route::get('scale', [SalaryController::class, 'salaryScale']);
            Route::post('scale/store', [SalaryController::class, 'storeSalaryScale']);
            Route::put('scale/update/{salary_scale}', [SalaryController::class, 'updateSalaryScale']);

            // Route::get('fetch-staff-with-salary-scale', [SalaryController::class, 'fetchStaffWithSalaryScale']);
            Route::put('assign-staff-salary-scale/{salary_scale}', [SalaryController::class, 'assignStaffSalaryScale']);


            Route::post('prepare-salary-sheet', [SalaryController::class, 'prepareSalarySheet']);
            Route::post('pay-staff', [SalaryController::class, 'payStaffSalary']);
            Route::get('payments-monitor', [SalaryController::class, 'salaryPaymentsMonitor']);
        });
        Route::group(['prefix' => 'revenue'], function () {


            Route::get('income-expenses', [AccountController::class, 'incomeAndExpenses']);
            Route::get('statement-of-account', [AccountController::class, 'statementOfAccount']);

            Route::post('add-income-expenses', [AccountController::class, 'addIncomeExpenses']);
            Route::get('expenses-recipients', [AccountController::class, 'getExpensesRecipient']);

            Route::put('approve-income-expenses/{income_expense}', [AccountController::class, 'approveIncomeExpense']);
            Route::delete('delete-income-expenses/{income_expense}', [AccountController::class, 'deleteExpenses']);
        });
    });

    //////////////////////DASHBOARD//////////////////////////
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('super', [DashboardsController::class, 'superAdminDashboard']);
        Route::get('admin', [DashboardsController::class, 'adminDashboard']);
        Route::get('student', [DashboardsController::class, 'studentDashboard']);
        Route::get('teacher', [DashboardsController::class, 'teacherDashboard']);
    });
    ///////////////////EVENTS/////////////////////////////////
    Route::group(['prefix' => 'events'], function () {

        Route::get('/', [EventsController::class, 'index']);
        Route::post('add-event', [EventsController::class, 'addEvent']);
        Route::delete('delete/{event}', [EventsController::class, 'deleteEvent']);
        Route::put('update/{event}', [EventsController::class, 'updateEvent']);
        Route::get('/upcoming-events', [EventsController::class, 'upcomingEvents']);
    });

    Route::group(['prefix' => 'messages'], function () {

        Route::get('/', [MessagesController::class, 'index']);
        Route::get('/sent', [MessagesController::class, 'sent']);
        Route::post('send-message', [MessagesController::class, 'store']);
        Route::delete('delete/{message}', [MessagesController::class, 'delete']);
        Route::put('update/{message}', [MessagesController::class, 'update']);
        Route::get('/details/{message}', [MessagesController::class, 'messageDetails']);
    });

    Route::group(['prefix' => 'curriculum'], function () {
        Route::post('level-group/save', [CurriculumCategoryController::class, 'storeCurriculumLevelGroup']);
        Route::post('level/save', [CurriculumCategoryController::class, 'storeCurriculumLevel']);
        Route::put('level/update/{curriculum_level}', [CurriculumCategoryController::class, 'updateCurriculumLevel']);

        Route::get('level-group/all', [CurriculumCategoryController::class, 'allCurriculumLevelGroups']);
        Route::get('level/all', [CurriculumCategoryController::class, 'allCurriculumLevels']);
        Route::put('level-group/update/{curriculum_level_group}', [CurriculumCategoryController::class, 'updateCurriculumLevelGroup']);
    });
     Route::group(['prefix' => 'attendance'], function () {
        Route::get('classes', [AttendanceController::class, 'classes']);
        Route::get('create/class', [AttendanceController::class, 'createClassAttendance']);
        Route::post('store/class', [AttendanceController::class, 'storeClassAttendance']);

        Route::get('subjects', [AttendanceController::class, 'subjects']);
        Route::get('create/subject', [AttendanceController::class, 'createSubjectAttendance']);
        Route::post('store/subject', [AttendanceController::class, 'storeSubjectAttendance']);
        //Route::get('fetch-level', 'AttendanceController@fetchLevelAttendanceChart');

    }); 
    Route::group(['prefix' => 'assignment'], function () {
        Route::get('/view-assignment', [AssignmentsController::class, 'index']);
        Route::get('/all-assignments', [AssignmentsController::class, 'allAssignments']);
        Route::get('fetch-subjects', [AssignmentsController::class, 'fetchSubjects']);
        Route::post('store', [AssignmentsController::class, 'store']);
        Route::post('score', [AssignmentsController::class, 'scoreAssignment']);
        // Route::put('update/{assignment}', [AssignmentsController::class, 'update']);
        Route::delete('destroy/{id}', [AssignmentsController::class, 'destroy']);
        Route::get('student/assignments', [AssignmentsController::class, 'studentAssignments']);

        Route::get('student/answer/{id}', [AssignmentsController::class, 'studentAnswerDetails']);
        Route::post('student/assignments/tackle', [AssignmentsController::class, 'tackleAssignment']);

        // Route::group(['middleware' => 'permission:admin~proprietor'],function() {

        //     Route::get('all-assignment', 'AssignmentsController@allAssignments')->name('all_assignments');


        // });
        // Route::group(['middleware' => 'permission:teacher'],function() {

        //     Route::resource('assignments', 'AssignmentsController');


        //     Route::get('teacher-class', 'AssignmentsController@teacherClassAssignment')->name('teacher_class_assignment');



        // });
        // Route::group(['middleware' => 'permission:teacher~admin~proprietor'], function() {
        //       Route::get('student/answer/{id}', 'AssignmentsController@studentAnswerDetails')->name('student_answer_details');

        //       Route::get('mark/{id}', 'AssignmentsController@getMark')->name('mark_assignment');
        // });
        // Route::group(['middleware' => 'permission:parent~teacher~admin~proprietor'], function() {



        //     Route::get('parent/student/assignment/{id}', 'AssignmentsController@studentAssignments')->name('parent_student_assignments');
        // });

        // Route::group(['middleware' => 'permission:student'], function() {



        //     Route::get('student/assignments', 'AssignmentsController@studentAssignments')->name('student_assignments');

        //     Route::get('tackle/assignment/{id}', 'AssignmentsController@tackleAssignmentForm')->name('tacle_assignment_form');






        // });
    });
    Route::group(['prefix' => 'library'], function () {

        Route::get('/fetch-data', [LibrariesController::class, 'fetchData']);
        Route::group(['prefix' => 'books'], function () {
            Route::get('/', [LibrariesController::class, 'books']);
            Route::post('store', [LibrariesController::class, 'storeBook']);
            Route::put('update/{book}', [LibrariesController::class, 'updateBook']);
            // Route::delete('destroy/{book}', [LibrariesController::class, 'destroyBook']);

            Route::get('/category', [LibrariesController::class, 'bookCategory']);
            Route::post('store-category', [LibrariesController::class, 'storeBookCategory']);
            Route::put('update-category/{category}', [LibrariesController::class, 'updateBookCategory']);
            // Route::delete('destroy-category/{category}', [LibrariesController::class, 'destroyBookCategory']);
        });
        Route::group(['prefix' => 'borrow'], function () {

            Route::get('/', [LibrariesController::class, 'borrowedBooks']);
            Route::post('new-borrowing', [LibrariesController::class, 'newBorrowing']);
            Route::put('update-borrowing/{book}', [LibrariesController::class, 'updateBorrowedBooks']);
            Route::put('return-book/{book}', [LibrariesController::class, 'returnBook']);
        });
    });
    Route::group(['prefix' => 'materials'], function () {
        Route::get('/teacher-curriculum', [CurriculaController::class, 'teacherCurriculum']);
        Route::post('/save-curriculum', [CurriculaController::class, 'store']);

        Route::get('/subject-curriculum/{subject_teacher}', [CurriculaController::class, 'subjectCurriculum']);
        Route::get('/teacher/subject-materials', [MaterialsController::class, 'teacherSubjectMaterials']);
        Route::post('/store', [MaterialsController::class, 'store']);
        Route::get('/subject-materials/{subject_teacher}', [MaterialsController::class, 'subjectMaterials']);
        Route::put('change-status/{material}', [MaterialsController::class, 'changeStatus']);

        Route::delete('/delete/{id}', [MaterialsController::class, 'destroy']);
    });
    Route::group(['prefix' => 'lms'], function () {
        Route::get('quiz', [QuizController::class, 'quiz']);
        Route::get('quiz-dashboard', [QuizController::class, 'quizDashboard']);
        Route::get('subject-teachers', [QuizController::class, 'subjectTeachers']);
        Route::get('student-quizzes', [QuizController::class, 'studentQuizzes']);

        Route::post('store-question', [QuizController::class, 'storeQuestion']);
        Route::put('update-question/{id}', [QuizController::class, 'updateQuestion']);
        Route::post('set-quiz', [QuizController::class, 'setQuiz']);
        Route::put('update-quiz/{id}', [QuizController::class, 'updateQuiz']);
        Route::put('activate-quiz/{id}', [QuizController::class, 'activateQuiz']);

        Route::delete('delete-quiz/{id}', [QuizController::class, 'deleteQuiz']);
        Route::post('attempt-quiz', [QuizController::class, 'attemptQuiz']);
        Route::post('update-remaining-time', [QuizController::class, 'updateRemainingTime']);
        Route::post('submit-quiz-answers', [QuizController::class, 'submitQuizAnswers']);
        Route::post('score-theory-answers', [QuizController::class, 'scoreTheoryAnswers']);


        Route::get('classroom', [ClassroomsController::class, 'index']);
        Route::get('teacher-routine', [ClassroomsController::class, 'teacherRoutine']);

        Route::post('create-online-class', [ClassroomsController::class, 'store']);
        Route::delete('delete-onlineclass/{id}', [ClassroomsController::class, 'deleteOnlineclass']);
        Route::post('upload-online-class-materials', [ClassroomsController::class, 'uploadOnlineClassMaterials']);

        Route::get('create-online-class-video', [ClassroomsController::class, 'createOnlineClassVideo']);
        Route::post('upload-online-class-video', [ClassroomsController::class, 'uploadOnlineClassVideo']);
        Route::post('update-online-class-note', [ClassroomsController::class, 'updateOnlineClassNote']);



        Route::delete('delete-onlineclass-material/{id}', [ClassroomsController::class, 'deleteOnlineclassMaterial']);
        Route::delete('delete-onlineclass-video/{id}', [ClassroomsController::class, 'deleteOnlineclassVideo']);
        Route::get('online-class-students/{id}', [ClassroomsController::class, 'onlineClassStudents']);

        Route::get('come-online/{id}', [ClassroomsController::class, 'comeOnline']);
        Route::post('post-in-online-class', [ClassroomsController::class, 'postInOnlineClass']);
        Route::delete('delete-classroom-post/{id}', [ClassroomsController::class, 'deleteClassroomPost']);

        Route::get('student-routine', [ClassroomsController::class, 'studentRoutine']);

        Route::get('created-online-classrooms', [ClassroomsController::class, 'createdOnlineClassrooms']);
    });

    Route::group(['prefix' => 'packages'], function () {


        Route::get('/', [PackagesController::class, 'index']);
        Route::post('store', [PackagesController::class, 'store']);
        Route::put('update/{package}', [PackagesController::class, 'update']);


        Route::get('fetch-modules', [PackagesController::class, 'fetchModules']);
        Route::post('add-module', [PackagesController::class, 'addModule']);
        Route::delete('remove-module/{package_module}', [PackagesController::class, 'removeModule']);

        Route::post('assign-school-package', [PackagesController::class, 'assignSchoolPackage']);
    });

    Route::group(['prefix' => 'report'], function () {

        Route::get('display-chart', [ReportsController::class, 'displayReportChart']);
        Route::get('attendance-report', [ReportsController::class, 'attendanceReport']);
    });

    Route::group(['prefix' => 'result'], function () {
        Route::resource('grades', GradesController::class);

        // Route::post('grades/store', GradesController::class, 'store');


        Route::get('set-selection-options', [ResultsController::class, 'setSelectionOptions']);
        Route::get('student-selection-options', [ResultsController::class, 'studentSelectionOptions']);
        Route::get('get-subject-students', [ResultsController::class, 'getSubjectStudent']);
        // Route::get('get-subject-students', [ResultsController::class, 'getSubjectStudentNew']);
        Route::post('record-result', [ResultsController::class, 'recordResult']);
        Route::post('normalize-result', [ResultsController::class, 'normalizeResult']);

        Route::post('result-action', [ResultsController::class, 'resultAction']);
        Route::post('upload-bulk-result', [ResultsController::class, 'uploadBulkResult']);
        Route::get('get-recorded-result', [ResultsController::class, 'getRecordedResultForApproval']);
        Route::get('class-broadsheet', [ResultsController::class, 'classBroadSheet']);
        Route::get('get-student-result-details', [ResultsController::class, 'getStudentResultDetails']);
        Route::get('give-student-remark', [ResultsController::class, 'giveStudentRemark']);
        Route::get('fetch-result-display-settings', [ResultDisplaySettingsController::class, 'index']);
        Route::post('update-result-display-settings', [ResultDisplaySettingsController::class, 'update']);


        // Route::post('level-group/save', [CurriculumCategoryController::class, 'storeCurriculumLevelGroup']);
        // Route::post('level/save', [CurriculumCategoryController::class, 'storeCurriculumLevel']);

        // Route::get('level-group/all', [CurriculumCategoryController::class, 'allCurriculumLevelGroups']);
        // Route::get('level/all', [CurriculumCategoryController::class, 'allCurriculumLevels']);
        // Route::put('level-group/update/{curriculum_level_group}', [CurriculumCategoryController::class, 'updateCurriculumLevelGroup']);
    });
    Route::group(['prefix' => 'schools'], function () {

        Route::get('/', [SchoolsController::class, 'index']);
        Route::get('/fetch-commumity', [SchoolsController::class, 'fetchSchoolCommunity']);
        Route::get('partner-schools', [SchoolsController::class, 'partnerSchools']);

        Route::get('/active', [SchoolsController::class, 'activeSchools']);

        Route::get('potential', [SchoolsController::class, 'potentialSchools']);
        Route::get('partner-potential-schools', [SchoolsController::class, 'partnerPotentialSchools']);
        Route::get('show/{school}', [SchoolsController::class, 'show']);
        Route::post('toggle-school-non-payment-suspension', [SchoolsController::class, 'toggleSchoolNonPaymentSuspension']);
        Route::post('set-school-arms', [SchoolsController::class, 'setArm']);

        Route::post('confirm-potential-school', [SchoolsController::class, 'confirmPotentialSchool']);
    });

    Route::group(['prefix' => 'school-setup'], function () {
        Route::get('set/color-code', [Controller::class, 'setColorCode']);
        Route::get('fetch-session-and-term', [Controller::class, 'fetchSessionAndTerm']);

        Route::get('levels', [LevelsController::class, 'index']);

        Route::get('fetch-level-class', [LevelsController::class, 'fetchLevelAndClass']);
        Route::get('fetch-specific-curriculum-level-groups', [LevelsController::class, 'fetchSpecificCurriculumLevels']);
        Route::get('fetch-curriculum-categories', [LevelsController::class, 'fetchCurriculumCategories']);
        Route::post('level/save', [LevelsController::class, 'store']);
        Route::put('level/update/{level}', [LevelsController::class, 'update']);
        Route::delete('level/destroy/{level}', [LevelsController::class, 'destroy']);

        Route::resource('classes', ClassesController::class);
        Route::post('class/assign-teacher', [ClassesController::class, 'assignClassTeacher']);

        Route::get('class-teacher-class', [ClassesController::class, 'classTeacherClasses']);
        Route::delete('class/destroy/{class_teacher}', [ClassesController::class, 'destroy']);

        Route::resource('sections', SectionsController::class);
        Route::resource('subjects', SubjectsController::class);
        Route::delete('subject/destroy/{subject}', [SubjectsController::class, 'destroy']);
        Route::get('fetch-teacher-subject', [SubjectsController::class, 'fetchTeacherSubject']);
        Route::put('assign-subject/{subject_teacher}', [SubjectsController::class, 'assignSubject']);
        Route::put('enable-subject/{subject}', [SubjectsController::class, 'enableSubject']);

        Route::post('session/activate', [SessionsController::class, 'activate']);
        Route::post('term/activate', [TermsController::class, 'activate']);

        ////////////////Registration PINs//////////////////////////////
        Route::get('students-pins', [RegistrationPinsController::class, 'studentsPins']);
        Route::get('staff-pins', [RegistrationPinsController::class, 'staffPins']);
        Route::post('store-pins', [RegistrationPinsController::class, 'store']);
        Route::put('change-status/{registrationPin}', [RegistrationPinsController::class, 'changeStatus']);

        Route::post('delete-pins', [RegistrationPinsController::class, 'destroy']);
        ///////////////Super Admin Session management///////////////////////
        Route::get('session/index', [SessionsController::class, 'index']);
        Route::post('session/store', [SessionsController::class, 'store']);
        Route::put('toggle-session-activation/{id}', [SessionsController::class, 'toggleSessionActivation']);
        Route::get('term/index', [TermsController::class, 'index']);
        Route::put('toggle-term-activation/{id}', [TermsController::class, 'toggleTermActivation']);
        /////////////////////////////////////////////////////////////////////////////////////////////
        Route::get('my-subject-students', [SubjectsController::class, 'mySubjectStudents']);
        Route::post('manage-subject-students', [SubjectsController::class, 'manageSubjectStudents']);
        Route::get('subject-teacher-subject', [SubjectsController::class, 'subjectTeachersSubjects']);
        Route::get('student-subject', [SubjectsController::class, 'studentSubjects']);

        Route::get('get-class-students', [ClassesController::class, 'getClassStudents']);
        Route::post('record-ratings', [ClassesController::class, 'recordRatings']);
        // update school logo
        Route::post('update-logo', [SchoolsController::class, 'updateLogo']);
        Route::put('update-color/{school}', [SchoolsController::class, 'saveGeneralSettings']);
    });

    Route::group(['prefix' => 'teacher'], function () {
        Route::get('sessional-staff-performance', [StaffController::class, 'sessionalStaffPerformance']);
        Route::get('performance-analysis', [StaffController::class, 'staffPerformanceAnalysis']);
        // Route::get('fetch-school-teachers', 'StaffController@fetchSchoolTeachers');
        // Route::get('set-staff-level-category', 'StaffController@setStaffLevelCategory');
        // Route::get('details/{id}', 'StaffController@show');
        // Route::get('performance-analysis', 'StaffController@staffPerformanceAnalysis');
        // Route::get('sessional-staff-performance', 'StaffController@sessionalStaffPerformance');

        // Route::post('new-staff', 'StaffController@store');
    });

    Route::group(['prefix' => 'timeline'], function () {
        Route::get('/', [TimelinesController::class, 'index']);
        Route::post('store', [TimelinesController::class, 'store']);
        Route::post('post-comment', [TimelinesController::class, 'postComment']);

        //Route::get('fetch-level', 'AttendanceController@fetchLevelAttendanceChart');


    });


    Route::group(['prefix' => 'time-table'], function () {
        Route::get('fetch-classes', [RoutinesController::class, 'fetchClasses']);
        Route::get('fetch-class-routine/{class_teacher_id}', [RoutinesController::class, 'fetchClassRoutine']);
        Route::post('store', [RoutinesController::class, 'store']);
        Route::post('update', [RoutinesController::class, 'updateRoutine']);
        Route::delete('destroy/{routine}', [RoutinesController::class, 'destroy']);
        Route::get('student/class-time-table', [RoutinesController::class, 'classTimeTable']);
        Route::get('teacher/time-table', [RoutinesController::class, 'teacherTimeTable']);
        //Route::get('fetch-level', 'AttendanceController@fetchLevelAttendanceChart');


    });
    Route::group(['prefix' => 'user-setup'], function () {

        Route::get('duplicate-students', [StudentsController::class, 'duplicateStudentsInClass']);
        Route::delete('remove-duplicate-student/{student_in_class}', [StudentsController::class, 'removeDuplicateStudent']);

        Route::get('all-students-table', [StudentsController::class, 'allStudentsTable']);
        Route::get('students/create', [StudentsController::class, 'create']);
        Route::post('students/store', [StudentsController::class, 'store']);
        Route::put('students/update/{student_in_class}', [StudentsController::class, 'update']);
        Route::put('student/change-class/{student_in_class}', [StudentsController::class, 'changeStudentClass']);


        Route::get('students/show/{student}', [StudentsController::class, 'show']);
        Route::post('students/upload/bulk', [StudentsController::class, 'uploadBulkStudents']);
        Route::put('toggle-studentship-status/{student}', [StudentsController::class, 'toggleStudentshipStatus']);

        Route::get('level-students', [StudentsController::class, 'levelStudents']);
        Route::post('promote-students', [StudentsController::class, 'promoteStudents']);
        Route::get('fetch-alumni', [StudentsController::class, 'fetchAlumni']);

        Route::get('admin-reset/password', [UsersController::class, 'adminResetUserPassword']);
        Route::put('reset/password/{user}', [UsersController::class, 'resetPassword']);
        Route::post('upload-photo', [UsersController::class, 'updatePhoto']);
        Route::put('approve-user/{user}', [UsersController::class, 'approveUser']);

        // Route::resource('staff', StaffController::class);
        Route::get('staff', [StaffController::class, 'index']);
        Route::get('staff/fetch', [StaffController::class, 'fetchStaff']);

        Route::get('staff/create', [StaffController::class, 'create']);
        Route::post('staff/store', [StaffController::class, 'store']);
        Route::get('staff/show/{staff}', [StaffController::class, 'show']);
        Route::put('staff/update/{staff}', [StaffController::class, 'update']);
        Route::delete('staff/destroy/{staff}', [StaffController::class, 'destroy']);

        Route::get('guardians', [GuardiansController::class, 'index']);
        Route::get('fetch-guardians', [GuardiansController::class, 'fetchGuardians']);
        Route::post('save-parent', [GuardiansController::class, 'store']);
        Route::put('update-parent/{user}', [GuardiansController::class, 'update']);
        Route::get('guardian/show/{guardian}', [GuardiansController::class, 'show']);
    });

    Route::group(['prefix' => 'guardian'], function () {
        Route::get('wards', [GuardiansController::class, 'guardianWards']);
    });


    Route::group(['prefix' => 'partners'], function () {
        Route::get('/', [PartnersController::class, 'index']);
        Route::post('register', [PartnersController::class, 'store']);
        Route::put('update/{partner}', [PartnersController::class, 'update']);
    });
});

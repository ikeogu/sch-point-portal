<?php

namespace App\Http\Controllers\LMS;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\ClassTeacher;
use App\Models\StudentsInClass;
use App\Models\Question;
use App\Models\TheoryQuestion;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizCompilation;
use App\Models\SubjectTeacher;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $user = $this->getUser();
        if ($user->hasRole('admin') || $user->hasRole('proprietor')) {
            return $this->render('lms::quiz.admin');
        }
        if ($user->hasRole('student')) {
            return $this->render('lms::quiz.student');
        }
        if ($user->hasRole('teacher')) {
            return $this->render('lms::quiz.teacher');
        }

        return $this->render('lms::index');
    }
    public function studentQuizzes()
    {
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        $student = $this->getStudent();
        $student_in_class = StudentsInClass::where([
            'school_id' => $school_id,
            'sess_id' => $sess_id,
            'student_id' => $student->id,
            //'term_id'=>$term_id,
        ])->first();
        $class_teacher_id = $student_in_class->class_teacher_id;
        $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class', 'quizCompilations' => function ($query) use ($sess_id, $term_id) {
            return $query->where(['term_id' => $term_id, 'sess_id' => $sess_id])->where('status', 'Active')->with([
                'quizzes.question', 'quizzes.theoryQuestion', 'subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class'
            ]);
        }])->where(['school_id' => $school_id, 'class_teacher_id' => $class_teacher_id])->get();

        return response()->json(compact('subject_teachers'), 200);
    }
    public function quizDashboard(Request $request)
    {
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        $school_id = $this->getSchool()->id;
        $user = $this->getUser();
        if ($user->hasRole('admin') || $user->hasRole('proprietor')) {
            $class_teacher_id = $request->class_teacher_id;
            // $class_teachers = ClassTeacher::with(['c_class', 'subjectTeachers.subject', 'subjectTeachers.questions', 'subjectTeachers.theoryQuestions', 'subjectTeachers.quizCompilations.quizzes', 'subjectTeachers.quizCompilations.quizAttempts.quizAnswers.question', 'subjectTeachers.quizCompilations.quizAttempts.quizAnswers.theoryQuestion', 'subjectTeachers.quizCompilations.quizAttempts.student.user'])->where('school_id', $school_id)->get();

            $class_teacher = ClassTeacher::with(['c_class', 'subjectTeachers.subject', 'subjectTeachers.questions', 'subjectTeachers.theoryQuestions', 'subjectTeachers.quizCompilations' => function ($query) use ($sess_id, $term_id) {
                return $query->where(['term_id' => $term_id, 'sess_id' => $sess_id])->with([
                    'quizzes', 'quizAttempts.quizAnswers.question', 'quizAttempts.quizAnswers.theoryQuestion', 'quizAttempts.student.user'
                ]);
            }])->where('school_id', $school_id)->find($class_teacher_id);

            return response()->json(compact('class_teacher'), 200);
        }
        if ($user->hasRole('student')) {

            $student = $this->getStudent();
            $student_in_class_obj = new StudentsInClass();
            $student_in_class = $student_in_class_obj->fetchStudentInClass($student->id, $sess_id, $term_id, $school_id);

            $subject_teachers = SubjectTeacher::where(['school_id' => $school_id, 'class_teacher_id' => $student_in_class->class_teacher_id])->get();

            $compiled_quizzes = [];
            foreach ($subject_teachers as $subject_teacher) {
                $subject_compiled_quizzes = QuizCompilation::with(['subjectTeacher.subject', 'subjectTeacher.classTeacher.c_class', 'quizzes.question', 'quizzes.theoryQuestion'])->where(['school_id' => $school_id, 'subject_teacher_id' => $subject_teacher->id, 'term_id' => $term_id, 'sess_id' => $sess_id, 'status' => 'Active'])->get();
                if ($subject_compiled_quizzes->isNotEmpty()) {
                    $compiled_quizzes = array_merge($subject_compiled_quizzes->toArray(), $compiled_quizzes);
                }
            }

            return response()->json(compact('compiled_quizzes'), 200);
        }
        if ($user->hasRole('teacher')) {
            $can_view_teacher = true;
            $teacher = $this->getStaff();
            $teacher_questions = Question::where('teacher_id', $teacher->id)->count();
            $compiled_quizzes = QuizCompilation::where(['school_id' => $school_id, 'teacher_id' => $teacher->id, 'term_id' => $term_id, 'sess_id' => $sess_id])->count();
            $quiz_attempts = QuizAttempt::join('quiz_compilations', 'quiz_compilations.id', '=', 'quiz_attempts.quiz_compilation_id')
                ->where(['quiz_compilations.teacher_id' => $teacher->id, 'quiz_compilations.term_id' => $term_id, 'quiz_compilations.sess_id' => $sess_id])->count();

            $subject_teachers = SubjectTeacher::where('teacher_id', $teacher->id)->get();
            return response()->json(compact('teacher_questions', 'compiled_quizzes', 'quiz_attempts', 'can_view_teacher', 'subject_teachers'), 200);
        }
    }
    public function subjectTeachers()
    {
        $user = $this->getUser();
        $school_id = $this->getSchool()->id;
        $sess_id = $this->getSession()->id;
        $term_id = $this->getTerm()->id;
        if ($user->hasRole('teacher')) {
            $teacher = $this->getStaff();

            // $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class', 'questions', 'theoryQuestions', 'quizCompilations.quizzes', 'quizCompilations.quizAttempts.quizAnswers.question', 'quizCompilations.quizAttempts.quizAnswers.theoryQuestion',  'quizCompilations.quizAttempts.student.user'])->where('teacher_id', $teacher->id)->get();
            $subject_teachers = SubjectTeacher::with(['subject', 'classTeacher.c_class', 'questions', 'theoryQuestions', /*'quizCompilations.quizzes',*/ 'quizCompilations' => function ($query) use ($sess_id, $term_id) {
                return $query->where(['term_id' => $term_id, 'sess_id' => $sess_id])->with([
                    'quizzes', 'quizAttempts.quizAnswers.question', 'quizAttempts.quizAnswers.theoryQuestion', 'quizAttempts.student.user'
                ]);
            }])->where(['teacher_id' => $teacher->id, 'school_id' => $school_id])->get();
            return response()->json(compact('subject_teachers'), 200);
        }
    }
    public function attemptQuiz(Request $request)
    {
        $school_id = $this->getSchool()->id;
        $student = $this->getStudent();
        $quiz_compilation_id = $request->quiz_compilation_id;
        $quiz_compilation = QuizCompilation::find($quiz_compilation_id);
        $subject_teacher_id = $quiz_compilation->subject_teacher_id;
        $quiz_attempt = QuizAttempt::with(['quizAnswers' => function ($query) use ($student) {
            return $query->where('student_id', $student->id)->with([
                'question', 'theoryQuestion'
            ]);
        }])->where(['student_id' => $student->id, 'quiz_compilation_id' => $quiz_compilation_id])->first();

        if (!$quiz_attempt) {
            $quiz_attempt = new QuizAttempt();
            $quiz_attempt->student_id = $student->id;
            $quiz_attempt->quiz_compilation_id = $quiz_compilation_id;
            $quiz_attempt->has_submitted = 'no';
            $quiz_attempt->remaining_time = $request->remaining_time;

            $quiz_attempt->save();

            $quizzes = Quiz::where(['school_id' => $school_id, 'quiz_compilation_id' => $quiz_compilation_id, 'subject_teacher_id' => $subject_teacher_id])->inRandomOrder()->get();

            $answers = [];
            foreach ($quizzes as $quiz) {
                $quiz_answer = new QuizAnswer();
                if ($quiz_compilation->question_type == 'theory') {
                    $quiz_answer->theory_question_id = $quiz->theory_question_id;
                } else {
                    $quiz_answer->question_id = $quiz->question_id;
                }
                $quiz_answer->quiz_attempt_id = $quiz_attempt->id;
                $quiz_answer->student_id = $student->id;
                $quiz_answer->registration_no = $student->registration_no;

                $quiz_answer->save();

                $answers[] = QuizAnswer::with(['question', 'theoryQuestion'])->find($quiz_answer->id);
            }
        } else {
            $answers = $quiz_attempt->quizAnswers;
        }

        return response()->json(compact('quiz_attempt', 'answers'), 200);
    }
    public function updateRemainingTime(Request $request)
    {
        $quiz_attempt = QuizAttempt::find($request->id);
        $quiz_attempt->remaining_time = $request->rt;
        $quiz_attempt->save();
    }

    private function percentScore($score, $total)
    {
        $percent_score = (int) (($score / $total) * 100);
        return $percent_score;
    }
    private function convertToPointLimit($limit, $percent_score)
    {
        $point = ($limit * $percent_score) / 100;
        return $point;
    }
    private function markExam($student_answer, $correct_answer)
    {
        $point = 0;
        if ($student_answer == $correct_answer) {
            $point = 1;
        }

        return $point;
    }
    public function submitQuizAnswers(Request $request)
    {
        $answers = $request->toArray();
        $student_score = 0;
        $total_score = 0;

        foreach ($answers as $answer) {
            $quiz_attempt_id = $answer['quiz_attempt_id'];
            $quiz_answer_id = $answer['id'];
            $student_answer = $answer['student_answer'];
            $correct_answer = $answer['question']['answer'];
            $point_earned = $this->markExam($student_answer, $correct_answer);
            $student_score += $point_earned;
            $total_score++;

            $quiz_answer = QuizAnswer::find($quiz_answer_id);
            $quiz_answer->student_answer = $student_answer;
            $quiz_answer->point_earned = $point_earned;
            $quiz_answer->save();
        }
        $quiz_attempt = QuizAttempt::find($quiz_attempt_id);

        $compiled_quiz = QuizCompilation::find($quiz_attempt->quiz_compilation_id);
        $limit = $compiled_quiz->point;
        $percent_score = $this->percentScore($student_score, $total_score);
        $student_point = $this->convertToPointLimit($limit, $percent_score);

        $quiz_attempt->has_submitted = 'yes';
        $quiz_attempt->remaining_time = 0;
        $quiz_attempt->percent_score = $percent_score;
        $quiz_attempt->student_point = $student_point;
        $quiz_attempt->score_limit = $limit;
        $quiz_attempt->save();
        return response()->json(compact('percent_score', 'student_point', 'limit'), 200);
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function storeQuestion(Request $request)
    {
        $question_type = $request->question_type;
        if ($question_type == 'theory') {
            $question = new TheoryQuestion();
            $question->school_id = $this->getSchool()->id;
            $question->subject_teacher_id = $request->subject_teacher_id;
            $question->teacher_id = $this->getStaff()->id;
            $question->question = $request->question;
            $question->point = $request->point;
            $question->question_type = $question_type;
            $question->save();
        } else {
            $question = new Question();
            $question->school_id = $this->getSchool()->id;
            $question->subject_teacher_id = $request->subject_teacher_id;
            $question->teacher_id = $this->getStaff()->id;
            $question->question = $request->question;
            $question->optA = $request->optA;
            $question->optB = $request->optB;
            $question->optC = $request->optC;
            $question->optD = $request->optD;
            $question->optE = $request->optE;
            $question->answer = $request->answer;
            $question->question_type = $question_type;
            $question->point = $request->point;
            $question->save();
        }


        return response()->json(compact('question'), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function updateQuestion(Request $request, $id)
    {
        $question_type = $request->question_type;
        if ($question_type == 'theory') {
            $question = TheoryQuestion::find($id);
            $question->question = $request->question;
            $question->point = $request->point;
            $question->save();
        } else {
            $question = Question::find($id);
            $question->question = $request->question;
            $question->optA = $request->optA;
            $question->optB = $request->optB;
            $question->optC = $request->optC;
            $question->optD = $request->optD;
            $question->optE = $request->optE;
            $question->answer = $request->answer;
            $question->question_type = $request->question_type;
            $question->point = $request->point;
            $question->save();
        }

        return response()->json(compact('question'), 200);
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    private function compileQuiz(Request $request)
    {

        $compilation = new QuizCompilation();
        $compilation->school_id = $this->getSchool()->id;
        $compilation->teacher_id = $this->getStaff()->id;
        $compilation->subject_teacher_id = $request->subject_teacher_id;
        $compilation->sess_id = $this->getSession()->id;
        $compilation->term_id = $this->getTerm()->id;
        $compilation->instructions = $request->instructions;
        $compilation->question_type = $request->question_type;
        $compilation->duration = $request->duration;
        $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->save();

        return $compilation;
    }
    /**
     * Show the specified resource.
     * @return Response
     */
    public function setQuiz(Request $request)
    {
        $compilation = $this->compileQuiz($request);

        $question_ids = $request->question_ids;
        $quizzes = [];
        foreach ($question_ids as $question_id) {
            $quiz = new Quiz();
            $quiz->school_id = $this->getSchool()->id;
            $quiz->subject_teacher_id = $request->subject_teacher_id;
            if ($compilation->question_type == 'theory') {
                $quiz->theory_question_id = $question_id;
            } else {
                $quiz->question_id = $question_id;
            }
            //$quiz->question_id = $question_id;
            $quiz->quiz_compilation_id = $compilation->id;

            $quiz->save();
            $quizzes[] = $quiz;
        }
        $compilation->quizzes = $quizzes;
        //$quiz_compilations = QuizCompilation::where('subject_teacher_id', $request->subject_teacher_id)->get();

        return response()->json(compact('compilation'), 200);
    }
    public function activateQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);

        // $compilation->instructions = $request->instructions;
        // $compilation->duration = $request->duration;
        // $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->save();

        return response()->json(compact('compilation'), 200);
    }
    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function updateQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);
        $compilation->quizzes()->delete();
        $compilation->instructions = $request->instructions;
        $compilation->duration = $request->duration;
        $compilation->point = $request->point;
        $compilation->status = $request->status;
        $compilation->save();

        $question_ids = $request->question_ids;
        $quizzes = [];
        foreach ($question_ids as $question_id) {
            $quiz = new Quiz();
            $quiz->school_id = $this->getSchool()->id;
            $quiz->subject_teacher_id = $request->subject_teacher_id;
            if ($compilation->question_type == 'theory') {
                $quiz->theory_question_id = $question_id;
            } else {
                $quiz->question_id = $question_id;
            }
            //$quiz->question_id = $question_id;
            $quiz->quiz_compilation_id = $compilation->id;

            $quiz->save();
            $quizzes[] = $quiz;
        }
        $compilation = $compilation->with(['quizzes', 'quizAttempts.quizAnswers.question', 'quizAttempts.quizAnswers.theoryQuestion', 'quizAttempts.student.user'])->find($compilation->id);
        return response()->json(compact('compilation'), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function deleteQuiz(Request $request, $id)
    {
        $compilation = QuizCompilation::find($id);
        $compilation->quizAnswers()->delete();
        $compilation->quizAttempts()->delete();
        $compilation->quizzes()->delete();
        $compilation->delete();

        return response()->json(['message' => 'success'], 200);
    }
    public function scoreTheoryAnswers(Request $request)
    {
        $staff_id = $this->getStaff()->id;
        $point_earned =  $request->point_earned;
        $id = $request->id;
        $answer = QuizAnswer::find($id);

        if ($staff_id == $answer->theoryQuestion->teacher_id) {
            $answer->point_earned = $point_earned;
            $answer->save();
            ////////////mark the percentage score///////////////////////////
            $quiz_attempt_id = $answer->quiz_attempt_id;
            $quiz_attempt = QuizAttempt::with(['student.user', 'quizAnswers.theoryQuestion'])->find($quiz_attempt_id);

            $total_score = 0; //QuizAnswer::where('quiz_attempt_id', $quiz_attempt_id)->sum('point_earned');
            $total_point_earned = 0;
            $all_answers = QuizAnswer::where('quiz_attempt_id', $quiz_attempt_id)->get();
            foreach ($all_answers as $all_answer) {
                $total_score += $all_answer->theoryQuestion->point;
                $total_point_earned += $all_answer->point_earned;
            }
            $compiled_quiz = QuizCompilation::find($quiz_attempt->quiz_compilation_id);
            $limit = $compiled_quiz->point;
            $percent_score = $this->percentScore($total_point_earned, $total_score);
            $student_point = $this->convertToPointLimit($limit, $percent_score);

            $quiz_attempt->has_submitted = 'yes';
            $quiz_attempt->remaining_time = 0;
            $quiz_attempt->percent_score = $percent_score;
            $quiz_attempt->student_point = $student_point;
            $quiz_attempt->score_limit = $limit;
            $quiz_attempt->save();

            return response()->json(compact('quiz_attempt'), 200);
        }
        return response()->json('You have no right to score this exam!!!', 403);
    }
    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
}

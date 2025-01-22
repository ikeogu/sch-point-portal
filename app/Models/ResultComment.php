<?php

namespace App\Models;

class ResultComment
{
    private $min;
    private $max;
    private $comments;

    /**
     * Commment constructor.
     * @param $min
     * @param $max
     * @param $comments
     */
    public function __construct($min, $max, $comments)
    {
        $this->min = $min;
        $this->max = $max;
        $this->comments = $comments;
    }

    public static function headTeacherComment()
    {
        return [
            new ResultComment(80, 100, [
                "%s is an excellent student and should keep up the momentum.", "%s is an enthusiastic learner who settles for nothing below excellence.",
                "%s tackles new challenges seriously with a positive attitude.", "%s's performance is exceptional and all I will say is keep it up!", "%s is an amazing student, and will truly shine!"
            ]),

            new ResultComment(60, 79.999, [
                "Impressive performance by %s. But there are more grounds to cover.",
                "%s shows perseverance in given tasks. This can improve future performances.", "Though an impressive performance, %s has the potential to do better than what is being obtained.", "Though impressive, %s should not settle for this. You can do better."
            ]),

            new ResultComment(50, 59.999, ["%s's performance is satisfactory but should not settle here. Aim higher!", "%s can do better than this. Put in more effort.", "%s has the potential to do better than what is being obtained.", "An average performance. %s, you can do better!", "A satisfactory performance. This is not your best %s."]),

            new ResultComment(40, 49.999, ["A fair result. Put in more effort %s.",  "%s did below average. Concentrate on your weak points.", "%s should avoid all distractions and stay focused.", "Performance is below average. %s should sit up."]),

            new ResultComment(0, 39.999, [
                "A weak performance. %s needs urgent attention.", "%s should urgently be attended to concerning this performance.",
                "I'm not impressed with %s's performance. Urgent attention should be given."
            ])
        ];
    }
    public static function getAllGeneralComment()
    {
        return [
            new ResultComment(80, 100, [
                "Excellent performance by %s", "Outstanding performance by %s",
                "Exceptional performance by %s", "%s's performance is exceptional",
            ]),

            new ResultComment(60, 79.999, [
                "A very good result by %s", "%s's performance is very impressive",
                "A remarkable performance by %s",
            ]),

            new ResultComment(50, 59.999, ["Satisfactory performance by %s", "%s has an average result", "%s did satisfactorily well", "%s did averagely well"]),

            new ResultComment(40, 49.999, ["%s has a fair result",  "%s did below average"]),

            new ResultComment(0, 39.999, [
                "%s did well below average", "%s has a poor result",
                "%s did poorly"
            ])
        ];
    }

    public static function getAllComplementaryComment()
    {
        return [
            new ResultComment(60, 69.999, ["can do better in %s", "should not relax in %s"]),

            new ResultComment(50, 59.999, ["needs to work on %s", "needs to improve on %s", "should try harder in %s"]),
            new ResultComment(0, 49.999, ["needs immediate attention on %s", "needs to extremely work hard in %s", "needs to put more effort in %s", "needs to wake up in %s"])
        ];
    }

    /**
     * @param $name
     * @param $grades
     * @param $average
     * @return string
     */
    public static function getComment($name, $grades, $average, $commentator = 'class_teacher')
    {
        $head_teacher_comments = self::headTeacherComment();
        $generals = self::getAllGeneralComment();
        $complements = self::getAllComplementaryComment();
        $comment = "";
        if ($commentator == 'head_teacher') {
            foreach ($head_teacher_comments as $head_teacher_comment) {
                if ($head_teacher_comment->isInRange($average)) {
                    $comment = $comment . $head_teacher_comment->getRandomSingleComment($name);
                }
            }
        }
        if ($commentator == 'class_teacher') {
            foreach ($generals as $general) {
                if ($general->isInRange($average)) {
                    $comment = $comment . $general->getRandomSingleComment($name);
                }
            }
        }

        return $comment;

        //        $comment = $comment . ( $generals[ count( $generals) - 1 ]->isInRange( $average ) ? " and " : " but " );

        // for ($i = 0; $i < count($complements); ++$i) {
        //     $commentGrade = array();
        //     foreach ($grades as $grade) {
        //         if ($complements[$i]->isInRange($grade["grade"])) {
        //             array_push($commentGrade, $grade);
        //         }
        //     }

        //     if (count($commentGrade) != 0) {

        //         if ($i == 0) {
        //             $comment = $comment . ($generals[count($generals) - 1]->isInRange($average) ? " and " : " but ");
        //             $comment = $comment . $complements[$i]->getRandomSingleComments($commentGrade);
        //         } else
        //             $comment = $comment . " and " . $complements[$i]->getRandomSingleComments($commentGrade);
        //     }
        // }

        // return $comment;
    }

    public static function getComplementaryComment($subjects)
    {
        $complements = self::getAllComplementaryComment();

        foreach ($complements as $comment) {
            $comment->getRandomComments($subjects);
        }
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param mixed $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    public function isInRange($grade)
    {
        return $this->getMin() <= $grade && $grade <= $this->getMax();
    }

    public function getRandomSingleComments($subjects)
    {
        $prefix = "";
        for ($i = 0; $i < count($subjects); ++$i) {
            if ($i == 0) {
                $prefix = $subjects[$i]["name"];
                continue;
            }
            if ($i == count($subjects) - 1) {
                $prefix = $prefix . " and " . $subjects[$i]["name"];
                continue;
            }
            $prefix = $prefix . ", " . $subjects[$i]["name"];
        }

        $index = rand(0, count($this->comments) - 1);
        return sprintf($this->comments[$index], $prefix);
    }

    public function getRandomSingleComment($subject)
    {
        $index = rand(0, count($this->comments) - 1);
        return sprintf($this->comments[$index], $subject);
    }
}

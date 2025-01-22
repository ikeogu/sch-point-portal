<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CClass extends Model
{
    use SoftDeletes;

    /**
     * Table name
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'school_id' .
            'level',
        'pre_level',
        'type'

    ];



    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function level()
    {
        return $this->belongsTo(Level::class, 'level', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function prelevel()
    {
        return $this->belongsTo(Level::class, 'pre_level', 'id');
    }


    public function getBritishClasses()
    {
    }

    public function getCurriculumClasses($school, $curriculum_array)
    {
        $nursery_class = collect([]);
        $primary_class = collect([]);
        $sec_class = collect([]);
        if (in_array('british', $curriculum_array)) {
            //fetch classes for british curriculum
            $british_class = $this->where('type', $school->type)
                ->orderBy('id')
                ->select('id', 'name')
                ->get();
            return $british_class;
        }
        if (in_array('nursery', $curriculum_array)) {
            //fetch classes for nursery curriculum

            $type = $school->type;
            $curr1 = 'Day Care';
            $curr2 = 'Reception';
            $curr3 = 'Nursery';
            $nursery_class = $this->where(function ($query) use ($curr1, $curr2, $curr3) {
                return $query->where('name', '=', $curr1)
                    ->orWhere('name', '=', $curr2)
                    ->orWhere('name', '=', $curr3);
            })->where('type', $school->type)
                ->groupBy(['name'])
                ->orderBy('id')
                ->select('id', 'name')
                ->get();
        }
        if (in_array('primary', $curriculum_array)) {
            //fetch classes for primary curriculum
            $primary_class = $this->where('type', $school->type)
                ->where('name', 'Primary')
                ->orderBy('id')
                ->select('id', 'name')
                ->get();
        }
        if (in_array('secondary', $curriculum_array)) {
            //fetch classes for secondary curriculum

            $type = $school->type;
            $curr1 = 'J.S.S';
            $curr2 = 'S.S.S';
            $sec_class = $this->where(function ($query) use ($curr1, $curr2) {
                return $query->where('name', '=', $curr1)
                    ->orWhere('name', '=', $curr2);
            })->where('type', $school->type)
                ->groupBy(['name'])
                ->orderBy('id')
                ->select('id', 'name')
                ->get();
        }

        $classes = $nursery_class->merge($primary_class)->merge($sec_class); //merge($nursery_class, $primary_class, $sec_class);

        return $classes;
    }

    public function getClassLevelSection($class_id, $school_id)
    {
        $class = $this->find($class_id);

        switch ($class->name) {
            case 'Day Care':
                $limit = 1;
                break;
            case 'Reception':
                $limit = 1;
                break;
            case 'Nursery':
                $limit = 2;
                break;
            case 'Primary':
                $limit = 6;
                break;
            case 'JSS':
                $limit = 3;
                break;
            case 'SSS':
                $limit = 3;
                break;
            default:
                $limit = 12;
        }

        $levels = Level::where('level', '<=', $limit)->get();

        $sections = Section::where('school_id',  $school_id)->get();


        return array($levels, $sections);
    }
}

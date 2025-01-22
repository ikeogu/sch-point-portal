<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumLevelGroup extends Model
{
    protected $table = 'curriculum_level_groups';
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function curriculumLevels()
    {
        return $this->hasMany(CurriculumLevel::class);
    }
    public function levels()
    {
        return $this->hasMany(Level::class);
    }
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
    public function curriculumCategory()
    {
        return $this->belongsTo(CurriculumCategory::class, 'curriculum_category_id', 'id');
    }
}

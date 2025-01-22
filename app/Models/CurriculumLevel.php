<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumLevel extends Model
{
    public function curriculumCategory()
    {
        return $this->belongsTo(CurriculumCategory::class, 'curriculum_category_id', 'id');
    }
    public function curriculumLevelGroup()
    {
        return $this->belongsTo(CurriculumLevelGroup::class, 'curriculum_level_group_id', 'id');
    }
}

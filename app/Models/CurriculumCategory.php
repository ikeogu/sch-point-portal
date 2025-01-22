<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumCategory extends Model
{
    public function curriculumLevels()
    {
        return $this->hasMany(CurriculumLevel::class, 'curriculum_category_id', 'id');
    }
    public function curriculumLevelGroups()
    {
        return $this->hasMany(CurriculumLevelGroup::class, 'curriculum_category_id', 'id');
    }
}

<?php

namespace App\Models;

use Laratrust\Models\Role as RoleModel;

class Role extends RoleModel
{
    public $guarded = [];

    public function isSuperAdmin(): bool
    {
        return $this->name === 'super';
    }
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }
}
<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name', 'description'];

    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[permissions.name,id,{id}]',
    ];
}

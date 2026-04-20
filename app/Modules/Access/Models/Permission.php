<?php

namespace App\Modules\Access\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('permissions')]
#[Fillable('name', 'description')]
class Permission extends Model
{
    use HasFactory, HasUuids;
}

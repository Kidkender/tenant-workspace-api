<?php

namespace App\Modules\Billing;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('plans')]
#[Fillable('name', 'price_monthly', 'price_yearly', 'max_users', 'features')]
class Plan extends Model
{
    use HasFactory, HasUuids;
    

}

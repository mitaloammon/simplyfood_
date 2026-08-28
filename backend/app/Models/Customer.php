<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'establishment_id',
        'name',
        'phone',
        'email',
        'document',
        'address',
    ];
}

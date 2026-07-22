<?php

namespace App\Domains\Customer\Address;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domains\Customer\Customer;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'cep',
        'address',
        'number',
        'neighborhood',
        'city',
        'state'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

<?php

namespace App\Domains\Auth\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Customer\Customer;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // ADMIN, OPERATOR, MANAGER, DELIVERY
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessors
    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function getIsOperatorAttribute(): bool
    {
        return $this->role === 'OPERATOR';
    }

    public function getIsManagerAttribute(): bool
    {
        return $this->role === 'MANAGER';
    }

    public function getIsDeliveryAttribute(): bool
    {
        return $this->role === 'DELIVERY';
    }

    // Mutator
    public function setRoleAttribute(string $value): void
    {
        $this->attributes['role'] = strtoupper($value);
    }

    // Scopes
    public function scopeOfRole(Builder $query, string $role): Builder
    {
        return $query->where('role', strtoupper($role));
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'ADMIN');
    }

    // Relationships
    // For demonstration, a user could have audits or customers
    public function auditLogs()
    {
        // To be defined in Audit Logs section
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

}

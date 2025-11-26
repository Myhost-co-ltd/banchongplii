<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Course;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'major',
        'homeroom',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Resolve the user's role name regardless of whether it comes from the
     * legacy `role` column or the roles relationship.
     */
    public function getRoleNameAttribute(): ?string
    {
        if (!empty($this->role_id)) {
            $role = $this->relationLoaded('role')
                ? $this->getRelation('role')
                : $this->role()->first();

            if ($role) {
                return $role->name;
            }
        }

        return $this->attributes['role'] ?? null;
    }

    public function hasRole($roles)
    {
        $roleName = $this->role_name;

        if (!$roleName) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($roleName, $roles);
        }

        return $roleName === $roles;
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the forms for the user.
     */
    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    /**
     * Get the fields for the user.
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the formulas for the user.
     */
    public function formulas()
    {
        return $this->hasMany(Formula::class);
    }

    /**
     * Get the bar bending locations for the user.
     */
    public function barBendingLocations()
    {
        return $this->hasMany(BarBendingLocation::class);
    }

    /**
     * Get the bar bending item details for the user.
     */
    public function barBendingItemDetails()
    {
        return $this->hasMany(BarBendingItemDetail::class);
    }

    /**
     * Get the bar bending form items for the user.
     */
    public function barBendingFormItems()
    {
        return $this->hasMany(BarBendingFormItem::class);
    }

    /**
     * Get the bar bending form locations for the user.
     */
    public function barBendingFormLocations()
    {
        return $this->hasMany(BarBendingFormLocation::class);
    }
}

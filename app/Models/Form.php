<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes, CreatedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_name',
        'unit',
        'rate',
        'group_by',
        'client_name',
        'project_name',
        'user_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * Get the fields for the form.
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the user that owns the form.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


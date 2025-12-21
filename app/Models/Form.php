<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_name',
        'unit',
        'client_name',
        'project_name',
    ];

    /**
     * Get the fields for the form.
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }
}


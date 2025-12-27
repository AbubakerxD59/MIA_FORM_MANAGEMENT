<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarBendingItemDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'form_id',
        'location_id',
        'name',
        'number',
        'width',
        'height',
        'length',
        'no_of_units',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'form_id' => 'integer',
            'location_id' => 'integer',
            'number' => 'integer',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'length' => 'decimal:2',
            'no_of_units' => 'integer',
        ];
    }

    /**
     * Get the form that owns the bar bending item detail.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the bar bending location for this item detail.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BarBendingLocation::class, 'location_id');
    }
}

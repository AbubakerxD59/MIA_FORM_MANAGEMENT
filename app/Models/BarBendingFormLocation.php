<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarBendingFormLocation extends Model
{
    use HasFactory, CreatedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'form_id',
        'user_id',
        'item_id',
        'location_id',
    ];

    /**
     * Get the form that owns the bar bending form location.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the bar bending form item that owns the location.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(BarBendingFormItem::class, 'item_id');
    }

    /**
     * Get the location for the bar bending form location.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BarBendingLocation::class);
    }

    /**
     * Get the user that owns the bar bending form location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarBendingFormItem extends Model
{
    use HasFactory, SoftDeletes, CreatedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'form_id',
        'user_id',
        'name',
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
        ];
    }

    /**
     * Get the form that owns the bar bending form item.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the locations for the bar bending form item.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(\App\Models\BarBendingFormLocation::class, 'item_id');
    }

    /**
     * Get the user that owns the bar bending form item.
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

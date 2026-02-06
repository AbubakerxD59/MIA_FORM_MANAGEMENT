<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CdHead extends Model
{
    use HasFactory, SoftDeletes, CreatedBy;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cd_head';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'form_id',
        'name',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * Get the user that owns the cd head.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the form that owns the cd head.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the cd items for the cd head.
     */
    public function cdItems(): HasMany
    {
        return $this->hasMany(CdItem::class, 'head_id');
    }

    /**
     * Get the cd summaries for the cd head.
     */
    public function cdSummaries(): HasMany
    {
        return $this->hasMany(CdSummary::class, 'head_id');
    }
}

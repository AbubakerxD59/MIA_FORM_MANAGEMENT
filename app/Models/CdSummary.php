<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CdSummary extends Model
{
    use HasFactory, CreatedBy;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cd_summary';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'form_id',
        'head_id',
        'cd_type',
        'amount',
        'dated',
        'description',
    ];

    protected $casts = [
        'dated' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * Get the user that owns the cd summary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the form that owns the cd summary.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the cd head that owns the cd summary.
     */
    public function cdHead(): BelongsTo
    {
        return $this->belongsTo(CdHead::class, 'head_id');
    }
}

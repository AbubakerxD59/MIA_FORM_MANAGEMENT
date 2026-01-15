<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use App\Models\Traits\CreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceSummary extends Model
{
    use HasFactory, SoftDeletes, CreatedBy;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_summary';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'invoice_id',
        'item_id',
        'rate_id',
        'quantity',
        'amount',
        'remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * Get the invoice that owns the invoice summary.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the invoice item that this summary belongs to.
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'item_id');
    }

    /**
     * Get the invoice rate that this summary belongs to.
     */
    public function invoiceRate(): BelongsTo
    {
        return $this->belongsTo(InvoiceRate::class, 'rate_id');
    }

    /**
     * Get the user that owns the invoice summary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

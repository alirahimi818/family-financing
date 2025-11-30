<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodicTransaction extends Model
{
    protected $fillable = ['user_id', 'title', 'amount', 'type', 'category_id', 'transaction_date', 'tag_ids', 'is_active'];

    protected $casts = [
        'amount' => 'decimal:2',
        'tag_ids' => 'array',
        'transaction_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

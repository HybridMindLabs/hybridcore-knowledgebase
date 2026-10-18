<?php

namespace Hybridcore\Knowledgebase\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KbFeedback extends Model
{
    protected $table = 'kb_feedback';

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'user_id',
        'is_helpful',
        'comment',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(KbArticle::class, 'article_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::creating(function (KbFeedback $feedback): void {
            if (empty($feedback->created_at)) {
                $feedback->created_at = now();
            }
        });
    }
}

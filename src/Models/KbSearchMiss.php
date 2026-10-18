<?php

namespace Hybridcore\Knowledgebase\Models;

use Illuminate\Database\Eloquent\Model;

class KbSearchMiss extends Model
{
    protected $table = 'kb_search_misses';

    public $timestamps = false;

    protected $fillable = [
        'query',
        'hits_count',
        'last_searched_at',
    ];

    protected $casts = [
        'hits_count' => 'integer',
        'last_searched_at' => 'datetime',
    ];
}

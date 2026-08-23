<?php

namespace App\Models;

use App\Models\Concerns\PertenceAGranja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    use PertenceAGranja;

    protected $table = 'auditoria';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['alteracoes' => 'array', 'created_at' => 'datetime'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

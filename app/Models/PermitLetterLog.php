<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitLetterLog extends Model
{
    protected $fillable =[
        'permit_letter_id',
        'status_tahapan',
        'description',
        'updated_by',
    ];

    public function permitLetter() : BelongsTo {
        return $this->belongsTo(PermitLetters::class);
    }
}

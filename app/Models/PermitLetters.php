<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 */
class PermitLetters extends Model
{
    protected $table = 'permit_letter';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        'uraian',
        'no_surat',
        'kategori',
        'nama_pt',
        'tanggal_masuk_berkas',
        'no_produk_mabes',
        'status_tahapan'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}

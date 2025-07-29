<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static factory()
 * @method static where(string $string, mixed $no_surat)
 * @method static find($id)
 * @method static orderBy(string $string, string $string1)
 * @method static create(mixed $data)
 */
class PermitLetters extends Model
{
    protected $table = 'permit_letters';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $timestamps = true;
    public $incrementing = true;
    protected $fillable = [
        'uraian',
        'no_surat',
        'kategori_permit_letter',
        'sub_kategori_permit_letter',
        'status_tahapan',
        'nama_pt',
        'tanggal',
        'produk_no_surat_mabes',
        'dokumen',
        'released_dokumen',
        'note',
        'upload_status',
        'user_id',
        'updated_by'
    ];
    public function user() : BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function editor() : BelongsTo 
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function logs() : HasMany 
    {
        return $this->hasMany(PermitLetterLog::class, 'permit_letter_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'note',
        'upload_status',
        'released_dokumen',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}

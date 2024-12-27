<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static factory()
 * @method static where(string $string, mixed $no_surat)
 * @method static find($id)
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
        'nama_pt',
        'tanggal',
        'produk_no_surat_mabes',
        'dokumen',
    ];

}

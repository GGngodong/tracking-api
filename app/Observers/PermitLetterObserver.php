<?php

namespace App\Observers;

use App\Models\PermitLetterLog;
use App\Models\PermitLetters;
use Illuminate\Support\Facades\Auth;

class PermitLetterObserver
{
    /**
     * Handle the PermitLetters "created" event.
     */
    public function created(PermitLetters $permitLetters): void
    {
        //
    }

    /**
     * Handle the PermitLetters "updated" event.
     */
    public function updated(PermitLetters $permitLetters): void
    {
        if ($permitLetters->wasChanged('status_tahapan')) {
            PermitLetterLog::create([
                'permit_letter_id' => $permitLetters->id,
                'status_tahapan' => $permitLetters->status_tahapan,
                'description' => $this->getStatusDescription($permitLetters->status_tahapan),
                'updated_by' => Auth::check() ? Auth::user()->name : 'system',
            ]);
        }
    }

    /**
     * Handle the PermitLetters "deleted" event.
     */
    public function deleted(PermitLetters $permitLetters): void
    {
        //
    }

    /**
     * Handle the PermitLetters "restored" event.
     */
    public function restored(PermitLetters $permitLetters): void
    {
        //
    }

    /**
     * Handle the PermitLetters "force deleted" event.
     */
    public function forceDeleted(PermitLetters $permitLetters): void
    {
        //
    }

    private function getStatusDescription(int $status): string 
    {
        return match ($status) {
            'Saran Polres' => 'Surat dalam tahap saran dari Polres',
            'Rekom. Polda' => 'Menunggu rekomendasi dari Polda',
            'Verifikasi 1' => 'Dokumen sedang diverifikasi oleh admin pertama',
            'Submit' => 'Dokumen telah disubmit oleh pemohon',
            'Draft' => 'Dokumen masih dalam tahap draft',
            'Penelitian Dokumen' => 'Dokumen sedang diteliti',
            'Verifikasi 2' => 'Verifikasi kedua sedang berlangsung',
            'Verifikasi 3' => 'Verifikasi ketiga sedang berlangsung',
            'Approval' => 'Dokumen menunggu approval',
            'Penomoran' => 'Surat sedang dinomori',
            'Release' => 'Surat telah diterbitkan',
            default => 'Status diperbarui',
        };
    }
}

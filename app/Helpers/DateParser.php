<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateParser
{
    public static function parseDate($dateString): ?string
    {
        try {
            $formats = ['d-m-Y', 'Y-m-d', 'm/d/Y'];

            foreach ($formats as $format) {
                $date = Carbon::createFromFormat($format, $dateString);

                if ($date) {
                    return $date->format('Y-m-d');
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error parsing date: " . $e->getMessage());
            return null;
        }
    }
}

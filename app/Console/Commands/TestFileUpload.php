<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestFileUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:file-upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test file upload functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create a simple file to upload
        $fileContent = 'This is a test file content.';

        // Attempt to store the file
        $filePath = Storage::disk('local')->put('test/testfile.txt', $fileContent);

        // Check if the file was uploaded successfully
        if ($filePath) {
            $this->info('File uploaded successfully to: ' . $filePath);
        } else {
            $this->error('File upload failed.');
        }
    }
}

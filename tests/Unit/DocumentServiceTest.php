<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentServiceTest extends TestCase
{
    public function test_document_is_classified_correctly()
    {
        Storage::fake('public');

        $service = app(DocumentService::class);

        $request = new class {
            public function all() { return []; }
            public function hasFile($key) { return true; }
            public function file($key) {
                return UploadedFile::fake()->create('baptism.pdf');
            }
        };

        $response = $service->store($request);

        $this->assertEquals(1, $response['error_code']);
    }
}

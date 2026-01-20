<?php

use App\Models\CertificateType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tcertificate_types', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_type');
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        $this->createCertificateTypes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcertificate_types');
    }

    private function createCertificateTypes()
    {
        CertificateType::create([
            'certificate_type' => 'Baptismal Certificate',
            'description' => 'Baptismal Certificate',
            'amount' => 100.00,
        ]);

        CertificateType::create([
            'certificate_type' => 'Marriage Certificate',
            'description' => 'Marriage Certificate',
            'amount' => 100.00,
        ]);

        CertificateType::create([
            'certificate_type' => 'Death Certificate',
            'description' => 'Death Certificate',
            'amount' => 100.00,
        ]);

        CertificateType::create([
            'certificate_type' => 'Confirmation Certificate',
            'description' => 'Confirmation Certificate',
            'amount' => 100.00,
        ]);
    }
};

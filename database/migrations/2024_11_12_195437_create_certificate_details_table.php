<?php

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
        Schema::create('tcertificate_details', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_type');
            $table->string('name_of_child')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('name_of_father')->nullable();
            $table->string('place_of_birth_father')->nullable();
            $table->string('name_of_mother')->nullable();
            $table->string('place_of_birth_mother')->nullable();
            $table->string('present_residence')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('sponsors')->nullable();
            $table->date('baptism_schedule')->nullable();
            $table->date('pickup_date')->nullable();
            $table->string('minister_baptism')->nullable();

            $table->string('bride_name')->nullable();
            $table->integer('age_bride')->nullable();
            $table->date('birthdate_bride')->nullable();
            $table->string('birthplace_bride')->nullable();
            $table->string('citizenship_bride')->nullable();
            $table->string('religion_bride')->nullable();
            $table->string('residence_bride')->nullable();
            $table->string('civil_status_bride')->nullable();
            $table->string('name_of_father_bride')->nullable();
            $table->string('name_of_mother_bride')->nullable();
            $table->string('name_of_groom')->nullable();
            $table->integer('age_groom')->nullable();
            $table->date('birthdate_groom')->nullable();
            $table->string('birthplace_groom')->nullable();
            $table->string('citizenship_groom')->nullable();
            $table->string('religion_groom')->nullable();
            $table->string('residence_groom')->nullable();
            $table->string('civil_status_groom')->nullable();
            $table->string('name_of_father_groom')->nullable();
            $table->string('name_of_mother_groom')->nullable();

            $table->string('first_name_death')->nullable();
            $table->string('middle_name_death')->nullable();
            $table->string('last_name_death')->nullable();
            $table->date('date_of_birth_death')->nullable();
            $table->date('date_of_death')->nullable();
            $table->date('death_schedule')->nullable();
            $table->date('pickup_date_death')->nullable();
            $table->string('minister_death')->nullable();
            $table->string('file_death')->nullable();

            $table->string('confirmation_first_name')->nullable();
            $table->string('confirmation_middle_name')->nullable();
            $table->string('confirmation_last_name')->nullable();
            $table->string('confirmation_place_of_birth')->nullable();
            $table->date('confirmation_date_of_baptism')->nullable();
            $table->string('confirmation_fathers_name')->nullable();
            $table->string('confirmation_mothers_name')->nullable();
            $table->date('confirmation_date_of_confirmation')->nullable();
            $table->string('confirmation_sponsors_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tcertificate_details');
    }
};

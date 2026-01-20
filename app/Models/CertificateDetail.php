<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateDetail extends Model
{
    protected $table = 'tcertificate_details';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'request_id',  // Foreign key to link to requests
        'certificate_type',

        // Baptismal Certificate
        'name_of_child',
        'date_of_birth',
        'place_of_birth',
        'baptism_schedule',
        'name_of_father',
        'name_of_mother',

        // Marriage Certificate
        // Bride Information
        'bride_name',
        'birthdate_bride',
        'age_bride',
        'birthplace_bride',
        'citizenship_bride',
        'religion_bride',
        'residence_bride',
        'civil_status_bride',
        'name_of_father_bride',
        'name_of_mother_bride',

        // Groom Information
        'name_of_groom',
        'birthdate_groom',
        'age_groom',
        'birthplace_groom',
        'citizenship_groom',
        'religion_groom',
        'residence_groom',
        'civil_status_groom',
        'name_of_father_groom',
        'name_of_mother_groom',

        // Death Certificate
        'first_name_death',
        'middle_name_death',
        'last_name_death',
        'date_of_birth_death',
        'date_of_death',
        'file_death',

        // Confirmation Certificate
        'confirmation_first_name',
        'confirmation_middle_name',
        'confirmation_last_name',
        'confirmation_place_of_birth',
        'confirmation_date_of_baptism',
        'confirmation_fathers_name',
        'confirmation_mothers_name',
        'confirmation_date_of_confirmation',
        'confirmation_sponsors_name',

        // Number of Copies
        'number_of_copies',
    ];
}

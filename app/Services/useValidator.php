<?php

namespace App\Services;

use Illuminate\Validation\Rule;

class useValidator
{
    public function priestValidator()
    {
        return [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'email_address' => 'required|email',
            'date_of_birth' => 'required|date',
            'phone_number' => 'required|string|max:255',
            'ordination_date' => 'required|date',
            'image' => 'nullable|file|mimes:png,jpg|max:2048',
        ];
    }

    public function donationValidator()
    {
        return [
            'donor_name' => 'required|string|max:255',
            'donor_email' => 'nullable|email',
            'donor_phone' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string',
            'transaction_id' => 'nullable|string|max:255',
        ];
    }

    public function mailValidator()
    {
        return [
            'title' => 'required|string|max:255',
            'sender' => 'required|string|email|max:255',
            'recipient' => 'required|string|email|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'date' => 'required|date',
        ];
    }

    public function documentValidator()
    {
        return [
            'full_name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:docx,pdf,jpg,jpeg,png|max:2048',
            'uploaded_by' => 'required|string|max:255',
        ];
    }

    public function requestValidator()
    {
        return [
            'document_type' => 'required|string|max:255',
            'requested_by' => 'required|string|max:255',
            'approved_by' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'is_paid' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:png,jpg|max:2048',

            // Baptismal Certificate
            'name_of_child' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'name_of_father' => 'nullable|string|max:255',
            'place_of_birth_father' => 'nullable|string|max:255',
            'name_of_mother' => 'nullable|string|max:255',
            'place_of_birth_mother' => 'nullable|string|max:255',
            'present_residence' => 'nullable|string|max:255',
            'sponsors' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'baptism_schedule' => 'nullable|date',
            'pickup_date' => 'nullable|date',
            'minister_baptism' => 'nullable|string|max:255',

            // Marriage Certificate
            // Bride Information
            'bride_name' => 'nullable|string|max:255',
            'age_bride' => 'nullable|string|max:255',
            'birthdate_bride' => 'nullable|date',
            'birthplace_bride' => 'nullable|string|max:255',
            'citizenship_bride' => 'nullable|string|max:255',
            'religion_bride' => 'nullable|string|max:255',
            'residence_bride' => 'nullable|string|max:255',
            'civil_status_bride' => 'nullable|string|max:255',
            'name_of_father_bride' => 'nullable|string|max:255',
            'name_of_mother_bride' => 'nullable|string|max:255',

            // Groom Information
            'groom_name' => 'nullable|string|max:255',
            'age_groom' => 'nullable|string|max:255',
            'birthdate_groom' => 'nullable|date',
            'birthplace_groom' => 'nullable|string|max:255',
            'citizenship_groom' => 'nullable|string|max:255',
            'religion_groom' => 'nullable|string|max:255',
            'residence_groom' => 'nullable|string|max:255',
            'civil_status_groom' => 'nullable|string|max:255',
            'name_of_father_groom' => 'nullable|string|max:255',
            'name_of_mother_groom' => 'nullable|string|max:255',

            // Burial Certificate
            'first_name_burial' => 'nullable|string|max:255',
            'middle_name_burial' => 'nullable|string|max:255',
            'last_name_burial' => 'nullable|string|max:255',
            'date_of_birth_burial' => 'nullable|date',
            'date_of_death_burial' => 'nullable|date',
            'burial_schedule' => 'nullable|date',
            'pickup_date_burial' => 'nullable|date',
            'minister_burial' => 'nullable|string|max:255',
            'file_burial' => 'nullable|file|mimes:docx,pdf,jpg,jpeg,png|max:2048',

            // Confirmation Certificate
            'confirmation_first_name' => 'nullable|string|max:255',
            'confirmation_middle_name' => 'nullable|string|max:255',
            'confirmation_last_name' => 'nullable|string|max:255',
            'confirmation_place_of_birth' => 'nullable|string|max:255',

            // Notes
            'notes' => 'nullable|string',
        ];
    }

    public function announcementValidator()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'assigned_priest' => 'required|string|max:255',
        ];
    }

    public function notificationValidator()
    {
        return [
            'type' => 'required|string|max:255',
            'message' => 'required|string',
            'is_read' => 'required|string|max:255',
        ];
    }
}

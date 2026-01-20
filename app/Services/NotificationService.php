<?php

namespace App\Services;

use App\Constant\MyConstant;
use App\Models\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationService
{
    private $validator;

    public function __construct(useValidator $validator)
    {
        $this->validator = $validator;
    }

    public function store($request)
    {
        $validator = Validator::make($request->all(), $this->validator->notificationValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            Notification::create([
                'message' => $request->input('message') . ' by ' . Auth::user()->name,
                'type' => $request->input('type'),
            ]);
        } catch (QueryException $e) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'error_code' => MyConstant::SUCCESS_CODE,
            'status_code' => MyConstant::OK,
            'message' => 'Notification created successfully',
        ];
    }
}

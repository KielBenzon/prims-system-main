<?php

namespace App\Services;

use App\Constant\MyConstant;
use App\Jobs\MailJob;
use App\Models\Mail;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private $validator;

    public function __construct(useValidator $validator)
    {
        $this->validator = $validator;
    }

    public function store($request)
    {
        $validator = Validator::make($request->all(), $this->validator->mailValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            $mail = Mail::create($request->all());
            $phpMailer = new PHPMailer(true);
            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.gmail.com';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
            $phpMailer->Password = 'hnzz zkkw zedc fxad';
            $phpMailer->SMTPSecure = 'tls';
            $phpMailer->Port = 587;
    
            $phpMailer->setFrom($mail->sender, $mail->sender);
            $phpMailer->addAddress($mail->recipient, $mail->recipient);
            $phpMailer->Subject = $mail->title;
            $phpMailer->Body = $mail->subject;
    
            $phpMailer->send();
            $mail->save();

            session()->flash('success', 'Mail created successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Mail created successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }

    public function update($request, $id)
    {
        $validator = Validator::make($request->all(), $this->validator->mailValidator());

        if ($validator->fails()) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::BAD_REQUEST,
                'message' => $validator->errors()->first(),
            ];
        }

        try {
            $mail = Mail::find($id);
            $phpMailer = new PHPMailer(true);
            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.gmail.com';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
            $phpMailer->Password = 'hnzz zkkw zedc fxad';
            $phpMailer->SMTPSecure = 'tls';
            $phpMailer->Port = 587;
    
            $phpMailer->setFrom($mail->sender, $mail->sender);
            $phpMailer->addAddress($mail->recipient, $mail->recipient);
            $phpMailer->Subject = $mail->title;
            $phpMailer->Body = $mail->subject;
    
            $phpMailer->send();
            $mail->update($request->all());
            $mail->save();

            session()->flash('success', 'Mail updated successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Mail updated successfully',
            ];
        } catch (QueryException $e) {
            session()->flash('error', 'Internal server error');
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $mail = Mail::find($id);
            $mail->delete();

            session()->flash('success', 'Mail deleted successfully');
            return [
                'error_code' => MyConstant::SUCCESS_CODE,
                'status_code' => MyConstant::OK,
                'message' => 'Mail deleted successfully',
            ];
        } catch (QueryException $e) {
            return [
                'error_code' => MyConstant::FAILED_CODE,
                'status_code' => MyConstant::INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }
    }
}

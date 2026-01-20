<?php

namespace App\Jobs;

use App\Models\Mail;
use App\Services\MailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use PHPMailer\PHPMailer\PHPMailer;

class MailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mail;

    /**
     * Create a new job instance.
     */
    public function __construct(Mail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $phpMailer = new PHPMailer(true);
        $phpMailer->isSMTP();
        $phpMailer->Host = 'smtp.gmail.com';
        $phpMailer->SMTPAuth = true;
        $phpMailer->Username = 'stmichaelthearcanghel@gmail.com';
        $phpMailer->Password = 'hnzz zkkw zedc fxad';
        $phpMailer->SMTPSecure = 'tls';
        $phpMailer->Port = 587;

        $phpMailer->setFrom($this->mail->sender, $this->mail->sender);
        $phpMailer->addAddress($this->mail->recipient, $this->mail->recipient);
        $phpMailer->Subject = $this->mail->title;
        $phpMailer->Body = $this->mail->subject;

        $phpMailer->send();
    }
}
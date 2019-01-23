<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     /**
     * @var
     */
    private $to;
    /**
     * @var array
     */
    private $payload;
    /**
     * @var
     */
    private $text;

    /**
     * Create a new job instance.
     *
     * @param $to
     * @param $text
     * @internal param array $payload
     */
    public function __construct($to, $text)
    {
        $this->to = encode_phone_number($to);
        $this->text = $text;
    }

  /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!env('APP_NOTIFY')){
            Log::info("SMS ==> ", ["to" => $this->to, "message" => $this->text]);
            return;
        }

        $this->send_message();
    }
}

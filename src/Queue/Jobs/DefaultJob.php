<?php

namespace Joblocal\LaravelSqsSnsSubscriptionQueue\Queue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DefaultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var array
     */
    private $payload;

    /**
     * @param string $subject
     * @param array $payload
     */
    public function __construct(string $subject, array $payload)
    {
        $this->subject = $subject;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}

<?php

namespace App\Jobs;

use App\Models\NewsletterSubscriber;
use App\Mail\NewsletterNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNewsletterNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $model;

    /**
     * Create a new job instance.
     *
     * @param string $type 'blog' or 'event'
     * @param mixed $model
     * @return void
     */
    public function __construct(string $type, $model)
    {
        $this->type = $type;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Starting SendNewsletterNotificationJob for {$this->type} id {$this->model->id}");

        // Fetch all active subscribers
        $subscribers = NewsletterSubscriber::where('status', 'active')->get();

        if ($subscribers->isEmpty()) {
            Log::info("No active subscribers found.");
            return;
        }

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterNotificationMail($this->type, $this->model));
            } catch (\Exception $e) {
                Log::error("Failed to send newsletter email to {$subscriber->email}: " . $e->getMessage());
            }
        }

        Log::info("Finished sending newsletter emails to {$subscribers->count()} subscribers.");
    }
}

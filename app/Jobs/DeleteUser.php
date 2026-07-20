<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteUser implements ShouldQueue
{
    private string $uid;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(string $uid)
    {
        $this->uid = $uid;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::where('uid', $this->uid)->first();
        $user->delete();

    }
}

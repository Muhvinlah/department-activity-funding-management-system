<?php

namespace App\Events;

use App\Models\Lpj;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LpjApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lpj $lpj;

    /**
     * Create a new event instance.
     */
    public function __construct(Lpj $lpj)
    {
        $this->lpj = $lpj;
    }
}

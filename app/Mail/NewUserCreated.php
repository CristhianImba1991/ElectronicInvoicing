<?php

namespace ElectronicInvoicing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserCreated extends Mailable
{
    use Queueable, SerializesModels;

    private $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('edgar.salguero@taotechideas.com')
                    ->subject($this->data['subject'])
                    ->markdown('vendor.notifications.email')
                    ->with([
                        'greeting' => $this->data['greeting'],
                        'level' => $this->data['level'],
                        'introLines' => $this->data['introLines'],
                        'outroLines' => $this->data['outroLines'],
                    ]);
    }
}

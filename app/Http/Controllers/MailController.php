<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{User, Voucher};
use ElectronicInvoicing\Mail\{NewUserCreated, NewVoucherIssued};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PDF;

class MailController extends Controller
{
    public static function sendMailNewUser(User $user, $password)
    {
        Mail::to($user->email)
            ->queue(new NewUserCreated(array(
                'subject' => 'YOUR USER HAS BEEN CREATED',
                'greeting' => 'Hello ' . $user->name . '!',
                'level' => 'primary',
                'introLines' => ['Your user has been created and you can use the following credentials to login.', '        E-Mail Address: ' . $user->email . "\n" . '        Password: ' . $password],
                'actionText' => 'ElectronicInvoicing',
                'actionUrl' => route('login'),
                'outroLines' => ['For your service, questions and information write an email to info@taotechideas.com.']))
            );
        return true;
    }

    public static function sendMailNewVoucher(Voucher $voucher)
    {
        $html = false;
        PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->save($voucher->accessKey() . '.pdf');
        Mail::to($voucher->customer->users->first()->email)
            ->cc(explode(',', $voucher->customer->email))
            ->bcc($voucher->user->email)
            ->queue(new NewVoucherIssued(array(
                'subject' => 'NEW VOUCHER FROM ' . strtoupper($voucher->emissionPoint->branch->company->social_reason) . ' TO ' . strtoupper($voucher->customer->social_reason),
                'greeting' => 'Hello ' . $voucher->customer->social_reason . '!',
                'level' => 'primary',
                'introLines' => ['You can find the voucher files in the attachments of this email, or you can view or download them from our system by clicking the following link.'],
                'actionText' => 'ElectronicInvoicing',
                'actionUrl' => route('login'),
                'outroLines' => ['For your service, questions and information write an email to info@taotechideas.com.'],
                'voucher' => $voucher))
            );
        File::delete(public_path($voucher->accessKey() . '.pdf'));
        return true;
    }
}

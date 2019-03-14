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
                'subject' => __('notification.your_user_has_been_created'),
                'greeting' => __('notification.hello_name', ['name' => $user->name]),
                'level' => 'primary',
                'introLines' => [__('notification.your_user_has_been_created_and_you_can_use_the_following_credentials_to_login'), __('notification.email_address_password', ['email' => $user->email, 'password' => $password])],
                'actionText' => config('app.name', 'Laravel'),
                'actionUrl' => route('login'),
                'outroLines' => [__('notification.for_your_service_questions_and_information_write_an_email_to_infotaotechideascom')]))
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
                'subject' => __('notification.new_voucher_from_company_to_customer', ['company' => strtoupper($voucher->emissionPoint->branch->company->social_reason), 'customer' => strtoupper($voucher->customer->social_reason)]),
                'greeting' => __('notification.hello_name', ['name' => $voucher->customer->social_reason]),
                'level' => 'primary',
                'introLines' => [__('notification.you_can_find_the_voucher_files_in_the_attachments_of_this_email')],
                'actionText' => config('app.name', 'Laravel'),
                'actionUrl' => route('login'),
                'outroLines' => [__('notification.for_your_service_questions_and_information_write_an_email_to_infotaotechideascom')],
                'voucher' => $voucher))
            );
        File::delete($voucher->accessKey() . '.pdf');
        return true;
    }
}

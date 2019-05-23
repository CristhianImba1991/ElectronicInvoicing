<?php

namespace ElectronicInvoicing\Http\Controllers;

use ElectronicInvoicing\{User, Voucher, VoucherType};
use ElectronicInvoicing\Mail\{NewUserCreated, NewVoucherIssued};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use PDF;
use Validator;

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
        $number = str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) . '-' .
            str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) . '-' .
            str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT);
        Mail::to($voucher->customer->users->first()->email)
            ->cc(explode(',', $voucher->customer->email))
            ->bcc($voucher->user->email)
            ->queue(new NewVoucherIssued(array(
                'subject' => trans_choice(__('notification.new_voucher_number_from_company_to_customer', ['voucher' => strtoupper(VoucherType::find($voucher->voucher_type_id)->name), 'number' => $number, 'company' => strtoupper($voucher->emissionPoint->branch->company->social_reason), 'customer' => strtoupper($voucher->customer->social_reason)]), in_array($voucher->voucher_type_id, [1, 2, 3, 4]) ? 1 : 0),
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

    public function sendMailVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:300|validemailmultiple',
            'voucher' => 'required|exists:vouchers,id'
        ]);
        $isValid = !$validator->fails();
        if ($isValid) {
            $html = false;
            $voucher = Voucher::find($request->voucher);
            PDF::loadView('vouchers.ride.' . $voucher->getViewType(), compact(['voucher', 'html']))->save($voucher->accessKey() . '.pdf');
            $number = str_pad(strval($voucher->emissionPoint->branch->establishment), 3, '0', STR_PAD_LEFT) . '-' .
                str_pad(strval($voucher->emissionPoint->code), 3, '0', STR_PAD_LEFT) . '-' .
                str_pad(strval($voucher->sequential), 9, '0', STR_PAD_LEFT);
            Mail::to(explode(',', $request->email))
                ->queue(new NewVoucherIssued(array(
                    'subject' => trans_choice(__('notification.new_voucher_number_from_company_to_customer', ['voucher' => strtoupper(VoucherType::find($voucher->voucher_type_id)->name), 'number' => $number, 'company' => strtoupper($voucher->emissionPoint->branch->company->social_reason), 'customer' => strtoupper($voucher->customer->social_reason)]), in_array($voucher->voucher_type_id, [1, 2, 3, 4]) ? 1 : 0),
                    'greeting' => __('notification.hello_name', ['name' => $voucher->customer->social_reason]),
                    'level' => 'primary',
                    'introLines' => [__('notification.you_can_find_the_voucher_files_in_the_attachments_of_this_email')],
                    'actionText' => config('app.name', 'Laravel'),
                    'actionUrl' => route('login'),
                    'outroLines' => [__('notification.for_your_service_questions_and_information_write_an_email_to_infotaotechideascom')],
                    'voucher' => $voucher))
                );
            File::delete($voucher->accessKey() . '.pdf');
        }
        return json_encode(array("status" => $isValid, "messages" => $validator->messages()->messages()));
    }
}

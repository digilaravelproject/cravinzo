<?php

namespace App\Traits;

use Exception;
use App\Models\Setting;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Storage;

trait  Processor
{
    public function response_formatter($constant, $content = null, $errors = []): array
    {
        $constant = (array)$constant;
        $constant['content'] = $content;
        $constant['errors'] = $errors;
        return $constant;
    }

    public function error_processor($validator): array
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $errors[] = ['error_code' => $index, 'message' => self::translate($error[0])];
        }
        return $errors;
    }

    public function translate($key)
    {
        try {
            App::setLocale('en');
            $lang_array = include(base_path('resources/lang/' . 'en' . '/lang.php'));
            $processed_key = ucfirst(str_replace('_', ' ', str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', $key)));
            if (!array_key_exists($key, $lang_array)) {
                $lang_array[$key] = $processed_key;
                $str = "<?php return " . var_export($lang_array, true) . ";";
                file_put_contents(base_path('resources/lang/' . 'en' . '/lang.php'), $str);
                $result = $processed_key;
            } else {
                $result = __('lang.' . $key);
            }
            return $result;
        } catch (\Exception $exception) {
            return $key;
        }
    }

    public function payment_config($key, $settings_type): object|null
    {
        try {
            $config = DB::table('addon_settings')->where('key_name', $key)
                ->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return new Setting();
        }

        return (isset($config)) ? $config : null;
    }

    public static function getDisk()
    {
        $config=\App\CentralLogics\Helpers::get_business_settings('local_storage');

        return isset($config)?($config==0?'s3':'public'):'public';
    }
    public function file_uploader(string $dir, string $format, $image = null, $old_image = null)
    {
        if ($image == null) return $old_image ?? 'def.png';

        if (isset($old_image)) Storage::disk(self::getDisk())->delete($dir . $old_image);

        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
        if (!Storage::disk(self::getDisk())->exists($dir)) {
            Storage::disk(self::getDisk())->makeDirectory($dir);
        }
        Storage::disk(self::getDisk())->put($dir . $imageName, file_get_contents($image));

        return $imageName;
    }

    public function payment_response($payment_info, $payment_flag): Application|JsonResponse|Redirector|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {
        $payment_info = PaymentRequest::find($payment_info->id);
        $token_string = 'payment_method=' . $payment_info->payment_method . '&&attribute_id=' . $payment_info->attribute_id . '&&transaction_reference=' . $payment_info->transaction_id;
        // Build token
        $encodedToken = base64_encode($token_string);
        // If external redirect link provided for app/web, prefer redirecting there
        if (in_array($payment_info->payment_platform, ['web', 'app']) && $payment_info['external_redirect_link'] != null) {
            $external = $payment_info['external_redirect_link'];
            // Determine separator depending on whether URL already has query params
            $sep = (strpos($external, '?') === false) ? '?' : '&';
            // Include both `status` and `flag` parameters for compatibility with different clients
            $query = $sep . 'status=' . $payment_flag . '&flag=' . $payment_flag . '&token=' . $encodedToken;
            // Log the full external redirect for debugging callback issues
            try {
                info('Payment external redirect: ' . $external . $query);
            } catch (\Exception $ex) {
                // swallow logging errors to avoid breaking flow
            }
            return redirect($external . $query);
        }
        // Log internal route redirect as well for debugging
        try {
            info('Payment internal redirect route: payment-' . $payment_flag . ' token=' . $encodedToken);
        } catch (\Exception $ex) {
        }
        return redirect()->route('payment-' . $payment_flag, ['token' => $encodedToken]);
    }
}

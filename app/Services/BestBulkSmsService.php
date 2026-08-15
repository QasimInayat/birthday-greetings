<?php

namespace App\Services;

use App\Models\SmsConfig;
use Illuminate\Support\Facades\Http;

class BestBulkSmsService
{
    protected $sendUrl;
    protected $balanceUrl;
    protected $statusUrl;
    protected $apiKey;
    protected $senderId;
    protected $route;
    protected $sourceUrl;
    protected $countryCode;

    public function __construct()
    {
        $config = SmsConfig::first();

        // Values saved on the SMS Configuration page win over the .env defaults.
        $this->sendUrl  = $this->pick($config->api_url ?? null, config('services.bestbulksms.send_url'));
        $this->apiKey   = $this->pick($config->api_key ?? null, config('services.bestbulksms.api_key'));
        $this->senderId = $this->pick($config->sender_id ?? null, config('services.bestbulksms.sender_id'));

        $this->balanceUrl  = config('services.bestbulksms.balance_url');
        $this->statusUrl   = config('services.bestbulksms.status_url');
        $this->route       = config('services.bestbulksms.route') ?: 'standard';
        $this->sourceUrl   = config('services.bestbulksms.source_url');
        $this->countryCode = config('services.bestbulksms.country_code');
    }

    /**
     * Send an SMS to one or many recipients.
     *
     * @param  string|array  $to
     */
    public function sendSMS($to, $message)
    {
        $recipients = $this->normalizeRecipients($to);

        if (empty($recipients)) {
            return ['status' => 'error', 'message' => 'No valid recipient phone number supplied.'];
        }

        $payload = [
            'sender_id' => $this->senderId,
            'to'        => $recipients,
            'message'   => $message,
            'route'     => $this->route,
        ];

        if (!empty($this->sourceUrl)) {
            $payload['source_url'] = $this->sourceUrl;
        }

        return $this->request('post', $this->sendUrl, $payload);
    }

    /**
     * Current wallet balance.
     */
    public function checkBalance()
    {
        return $this->request('get', $this->balanceUrl);
    }

    /**
     * Delivery status of a previously sent message.
     */
    public function messageStatus($smsMessageId)
    {
        return $this->request('get', $this->statusUrl, ['sms_message_id' => $smsMessageId]);
    }

    /**
     * True when the gateway reported a successful call.
     */
    public static function wasSuccessful($response): bool
    {
        return is_array($response) && ($response['status'] ?? null) === 'success';
    }

    /**
     * Human readable error text from a failed response.
     */
    public static function errorMessage($response): string
    {
        if (!is_array($response)) {
            return 'Unknown error.';
        }

        return $response['message'] ?? $response['error'] ?? json_encode($response);
    }

    /**
     * Turn a string, comma separated list or array into gateway-ready numbers.
     */
    public function normalizeRecipients($to): array
    {
        // Split on separators only - numbers may legitimately contain spaces, e.g. "+234 809 876 5432".
        $list = is_array($to) ? $to : preg_split('/[,;\r\n|]+/', (string) $to);
        $recipients = [];

        foreach ((array) $list as $number) {
            $number = $this->normalizeNumber($number);

            if ($number !== '' && !in_array($number, $recipients, true)) {
                $recipients[] = $number;
            }
        }

        return $recipients;
    }

    /**
     * Strip formatting and expand a local number to international format.
     */
    protected function normalizeNumber($number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '' || $digits === null) {
            return '';
        }

        if (!empty($this->countryCode)) {
            if (str_starts_with($digits, '00')) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0')) {
                $digits = $this->countryCode . substr($digits, 1);
            }
        }

        // Guard against fragments of a mistyped number reaching the gateway.
        if (strlen($digits) < 10) {
            return '';
        }

        return $digits;
    }

    /**
     * Perform the call and always hand back an array.
     */
    protected function request($method, $url, array $data = [])
    {
        if (empty($url)) {
            return ['status' => 'error', 'message' => 'SMS API endpoint is not configured.'];
        }

        if (empty($this->apiKey)) {
            return ['status' => 'error', 'message' => 'SMS API key is not configured.'];
        }

        try {
            $request = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout(30);

            $response = $method === 'get'
                ? $request->get($url, $data)
                : $request->post($url, $data);

            $body = $response->json();

            if (!is_array($body)) {
                return [
                    'status'  => 'error',
                    'message' => 'Unexpected response from SMS gateway (HTTP ' . $response->status() . ').',
                    'raw'     => $response->body(),
                ];
            }

            if ($response->failed() && ($body['status'] ?? null) !== 'success') {
                $body['status'] = 'error';
                $body['message'] = $body['message'] ?? 'SMS gateway returned HTTP ' . $response->status() . '.';
            }

            return $body;
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * First non-empty value.
     */
    protected function pick($value, $fallback)
    {
        return ($value === null || $value === '') ? $fallback : $value;
    }
}

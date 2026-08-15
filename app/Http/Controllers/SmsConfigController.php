<?php

namespace App\Http\Controllers;

use App\Models\SmsConfig;
use Illuminate\Http\Request;
use App\Services\BestBulkSmsService;


class SmsConfigController extends Controller
{
    // Show Config Page
    public function index()
    {
        $config = SmsConfig::first();
        return view('sms_config.index', compact('config'));
    }

    // Save or Update Config
    public function update(Request $request)
    {
        $existing = SmsConfig::first();

        $request->validate([
            'api_url'   => 'required|url',
            // Only required the first time - a blank field keeps the stored key.
            'api_key'   => ($existing && $existing->api_key) ? 'nullable|string' : 'required|string',
            'sender_id' => 'required|string|max:20'
        ]);

        $data = [
            'api_url'   => $request->api_url,
            'sender_id' => $request->sender_id,
        ];

        if (filled($request->api_key)) {
            $data['api_key'] = $request->api_key;
        }

        SmsConfig::updateOrCreate(['id' => 1], $data); // single record

        return redirect()->route('sms-config.index')
            ->with('success', 'SMS API configuration saved successfully.');
    }

    // Send a test SMS to the number entered on the config page
    public function test(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $sms = new BestBulkSmsService();
        $response = $sms->sendSMS($request->phone, 'Test message from ' . config('app.name') . ' via BestBulkSMS.');

        if (BestBulkSmsService::wasSuccessful($response)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Test SMS sent successfully. Message ID: ' . ($response['sms_message_id'] ?? 'n/a')
                    . ', units billed: ' . ($response['units_billed'] ?? 'n/a') . '.',
                'data'    => $response,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Test SMS failed: ' . BestBulkSmsService::errorMessage($response),
            'data'    => $response,
        ], 422);
    }

    // Wallet balance
    public function balance()
    {
        $sms = new BestBulkSmsService();
        $response = $sms->checkBalance();

        if (BestBulkSmsService::wasSuccessful($response)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Wallet balance retrieved successfully.',
                'data'    => $response,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Could not fetch balance: ' . BestBulkSmsService::errorMessage($response),
            'data'    => $response,
        ], 422);
    }

    // Delivery status of a sent message
    public function messageStatus(Request $request)
    {
        $request->validate([
            'sms_message_id' => 'required',
        ]);

        $sms = new BestBulkSmsService();
        $response = $sms->messageStatus($request->sms_message_id);

        if (BestBulkSmsService::wasSuccessful($response)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Message status retrieved successfully.',
                'data'    => $response['data'] ?? $response,
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Could not fetch message status: ' . BestBulkSmsService::errorMessage($response),
            'data'    => $response,
        ], 422);
    }
}

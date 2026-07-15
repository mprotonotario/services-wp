<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Enqueue a WhatsApp message to be sent asynchronously.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number'  => 'required|string|min:8',
            'message' => 'required|string|min:1',
        ]);

        // Clean the number format if necessary (e.g. remove spaces, plus sign, etc.)
        // Evolution API usually expects the number with the country code, without '+' or spaces.
        $number = preg_replace('/[^0-9]/', '', $validated['number']);

        // Dispatch the job
        SendWhatsAppMessageJob::dispatch($number, $validated['message']);

        return response()->json([
            'status' => 'success',
            'message' => 'El mensaje ha sido encolado para su envío.',
            'data' => [
                'number' => $number,
            ]
        ], 202);
    }
}

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

        // Clean the number format (remove non-digits like '+', spaces, etc.)
        $number = preg_replace('/[^0-9]/', '', $validated['number']);

        // Normalización automática para números de México:
        // 1. Si empieza con 521 y tiene 13 dígitos (formato obsoleto), remover el '1' intermedio
        if (str_starts_with($number, '521') && strlen($number) === 13) {
            $number = '52' . substr($number, 3);
        }
        // 2. Si solo tiene 10 dígitos, asumir que es de México y añadirle el prefijo de país 52
        elseif (strlen($number) === 10) {
            $number = '52' . $number;
        }

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

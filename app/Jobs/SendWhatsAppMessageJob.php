<?php

namespace App\Jobs;

use App\Services\EvolutionApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $number,
        protected string $message
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EvolutionApiService $apiService): void
    {
        Log::info("Iniciando envío de WhatsApp a {$this->number}. Aplicando lógica anti-bloqueo...");

        // 1. Retardo inicial aleatorio para simular pausa humana antes de interactuar (3 a 8 segundos)
        $initialDelay = rand(3, 8);
        sleep($initialDelay);

        // 2. Calcular tiempo de escritura en base a la longitud del mensaje.
        // Velocidad aproximada: 25 a 55 ms por caracter.
        $charCount = mb_strlen($this->message);
        $msPerChar = rand(25, 55);
        $typingDelayMs = $charCount * $msPerChar;

        // Limitar retardo de escritura a un máximo de 15 segundos
        $typingDelayMs = min($typingDelayMs, 15000);

        // 3. Enviar el mensaje real con la duración del estado "escribiendo" (typingDelayMs)
        $result = $apiService->sendText($this->number, $this->message, $typingDelayMs);

        if (!$result) {
            Log::error("Fallo al enviar el mensaje de WhatsApp a {$this->number}");
            throw new \Exception("Error al enviar mensaje a través de Evolution API");
        }

        // 4. Retardo final aleatorio posterior al envío para simular lectura o descanso (1 a 3 segundos)
        $postDelay = rand(1, 3);
        sleep($postDelay);

        Log::info("Mensaje enviado con éxito a {$this->number}");
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware(): array
    {
        // Esto evita que más de un Job de envío de WhatsApp corra al mismo tiempo.
        // Si entra otro mensaje mientras uno se está enviando, se libera y vuelve a la cola 
        // para ser procesado después de un breve momento.
        return [
            (new WithoutOverlapping('whatsapp-sending-lock'))->releaseAfter(10)
        ];
    }
}

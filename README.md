# Microservicio Laravel + Evolution API (Envíos Masivos con Anti-Bloqueo)

Este microservicio en Laravel está diseñado para encolar y enviar mensajes de WhatsApp a través de **Evolution API**, implementando una robusta lógica de simulación humana para mitigar el riesgo de bloqueo de números telefónicos por parte de WhatsApp.

## Características

1. **Cola de Envíos (Jobs)**: Los mensajes no se envían de forma síncrona. Se delegan a una cola gestionada por Redis, lo que permite responder instantáneamente a la API externa que originó la petición.
2. **Dockerizado**: Un entorno autocontenido mediante Docker Compose que levanta Laravel (Web Server + Queue Worker), Redis (gestor de colas) y la propia Evolution API.
3. **Lógica Anti-Bloqueo**:
   - **Retardo Inicial Aleatorio**: Espera aleatoria de entre 3 a 8 segundos antes de iniciar cualquier interacción con el destinatario.
   - **Simulación de Escritura (Composing)**: Envía el estado `composing` (escribiendo) al chat de WhatsApp y calcula un tiempo de espera dinámico y aleatorio proporcional al largo del mensaje (de 25 a 55 ms por caracter), simulando digitación humana en tiempo real.
   - **Retardo de Lectura Post-Envío**: Pausa aleatoria de entre 1 a 3 segundos posterior al envío antes de finalizar el job (simulando descanso o lectura del chat).

---

## Requisitos
- [Docker](https://www.docker.com/) instalado en tu equipo.

---

## Cómo Levantar el Microservicio

1. Abre una terminal en la raíz del proyecto.
2. Levanta los contenedores:
   ```bash
   docker compose up -d --build
   ```
   *Esto compilará la imagen de Laravel, configurará el worker de colas, levantará una instancia de Redis y una instancia limpia de la Evolution API.*

---

## Configuración

En el archivo `.env` o en la sección `environment` del servicio `app` en `docker-compose.yml`, puedes personalizar las siguientes variables:

- `EVOLUTION_API_URL`: La dirección de Evolution API (por defecto interna `http://evolution:8080`).
- `EVOLUTION_API_KEY`: La clave API global configurada en la Evolution API (por defecto `apikey_global_aqui`).
- `EVOLUTION_INSTANCE_NAME`: El nombre de la instancia conectada a WhatsApp (por defecto `laravel`).

---

## Uso de la API (Endpoint)

Para encolar un mensaje de WhatsApp, realiza una petición `POST` al endpoint del microservicio:

### Endpoint
`POST http://localhost:8000/api/messages/send`

### Cabeceras
- `Content-Type: application/json`
- `Accept: application/json`

### Body (JSON)
```json
{
  "number": "573000000000",
  "message": "Hola, este es un mensaje de prueba con simulación de escritura humana para evitar bloqueos."
}
```

### Respuesta Exitosa (`202 Accepted`)
```json
{
  "status": "success",
  "message": "El mensaje ha sido encolado para su envío.",
  "data": {
    "number": "573000000000"
  }
}
```

---

## Estructura del Código Clave

- [EvolutionApiService.php](file:///Users/miguel/Documents/proyectos/microservice/app/Services/EvolutionApiService.php): Administra la interacción por HTTP hacia los endpoints `/chat/sendPresence/` y `/message/sendText/`.
- [SendWhatsAppMessageJob.php](file:///Users/miguel/Documents/proyectos/microservice/app/Jobs/SendWhatsAppMessageJob.php): Contiene toda la lógica secuencial y asíncrona de retrasos aleatorios y simulación de tipeo.
- [MessageController.php](file:///Users/miguel/Documents/proyectos/microservice/app/Http/Controllers/MessageController.php): Valida la petición de entrada y despacha el job a la cola.
- [docker-entrypoint.sh](file:///Users/miguel/Documents/proyectos/microservice/docker-entrypoint.sh): Script de arranque que activa simultáneamente el servidor HTTP de Laravel y el worker de colas (`php artisan queue:work`) para procesar los envíos en segundo plano tan pronto el contenedor inicia.

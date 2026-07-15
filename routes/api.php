<?php

use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/messages/send', [MessageController::class, 'send']);


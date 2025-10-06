<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Vexim Global API',
        'version' => '1.0.0',
        'status' => 'active'
    ]);
});

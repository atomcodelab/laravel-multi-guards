<?php

use Illuminate\Support\Facades\Route;

// Import web.api routes
include 'webapi/admin.php';
include 'webapi/user.php';

// Page routes
Route::get('/', function () {
    return view('welcome');
});

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes([
    'register' => false
]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard/webhooks', [App\Http\Controllers\DashboardController::class, 'webhooks']);
Route::get('/dashboard/configdump', [App\Http\Controllers\DashboardController::class, 'configdump']);
Route::get('/dashboard/assets', [App\Http\Controllers\DashboardController::class, 'assets']);
Route::get('/config', function(Illuminate\Http\Request $request){
   $ary = \Illuminate\Support\Arr::flatten(config('middleware.field_mappings'));
   $ary = array_filter($ary, function($n){
       return !is_bool($n);
   });
   $ary = array_filter($ary, function($n){
       return !empty($n);
   });

   $ary = array_values($ary);
   //asort($ary);
   return $ary;
});

Route::get('/contactform', [App\Http\Controllers\FormController::class, 'contactform']);
Route::post('/contactform', [App\Http\Controllers\FormController::class, 'contactform']);

Route::get('/subscribe', [App\Http\Controllers\FormController::class, 'subscribeform']);
Route::post('/subscribe', [App\Http\Controllers\FormController::class, 'subscribeform']);

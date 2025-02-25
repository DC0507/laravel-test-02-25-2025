<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalsifyController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth')->group(function(){
    Route::get('/processrecipes', [\App\Http\Controllers\RecipeController::class, 'processRecipes']);
});

Route::get('/status', [SalsifyController::class, 'apiStatus']);

Route::middleware(['salsify.verify'])->group(function(){
    Route::get('/webhook', [SalsifyController::class, 'dummyWebhook']);
    Route::post('/channelpublished', [SalsifyController::class, 'channelPublished']);
    Route::post('/webhook', [SalsifyController::class, 'dummyWebhook']);
    Route::post('/productsadded', [SalsifyController::class, 'incomingSalsifyWebhookProductsAdded']);
    Route::post('/propertieschanged', [SalsifyController::class, 'incomingSalsifyWebhookPropertiesChanged']);
    Route::post('/productsdeleted', [SalsifyController::class, 'incomingSalsifyWebhookProductsDeleted']);
});

Route::middleware(['webhook.verify'])->group(function(){
    Route::get('/search', [SearchController::class, 'generateFeed']);
    Route::get('/search/products', [SearchController::class, 'products']);
    Route::get('/search/recipes', [SearchController::class, 'recipes']);

    Route::get('/storyblok', [SearchController::class, 'storyblok']);

    Route::post('/search/reindex', [SearchController::class, 'initiateSearchReindex']);
    Route::get('/search/reindex', [SearchController::class, 'initiateSearchReindex']);
});
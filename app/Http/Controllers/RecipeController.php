<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessRecipeFile;
use Illuminate\Http\Request;
use Storyblok\Client;
use Storyblok\ManagementClient;

class RecipeController extends Controller
{
    public function processRecipes(Request $request, Client $client, ManagementClient $mgmtClient)
    {
        ProcessRecipeFile::dispatchNow('drupal-recipes-raw.json', $client, $mgmtClient);

        return ['status' => 0, 'msg' => 'ok'];
    }
}

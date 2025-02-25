<?php

namespace App\Http\Controllers;

use App\Models\Salsify\Asset;
use App\Models\Salsify\Webhook;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function webhooks(Request $request)
    {
        return view('dashboard/webhooks', [
            'webhooks' => Webhook::query()->orderBy('created_at', 'DESC')->paginate(25),
        ]);
    }

    public function assets(Request $request)
    {
        return view('dashboard/assets', [
            'assets' => Asset::query()->orderBy('created_at', 'DESC')->paginate(25)
        ]);
    }

    public function configdump(Request $request)
    {
        $keys = [
            'STORYBLOK_SPACE_ID',
            'STORYBLOK_PRODUCT_SLUG_PREFIX',
            'STORYBLOK_RECIPE_SLUG_PREFIX',
            'STORYBLOK_PRODUCT_PARENT_ID',
            'STORYBLOK_RECIPE_PARENT_ID',
        ];
        $ret = [];
        foreach($keys as $key)
        {
            $ret[$key] = env($key);
        }
        $ret['middleware'] = config('middleware');
        unset($ret['middleware']['storyblok']['api_key']);
        unset($ret['middleware']['storyblok']['mgmt_api_key']);
        return $ret;
    }
}

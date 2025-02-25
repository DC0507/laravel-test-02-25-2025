<?php

namespace App\Http\Controllers;

use App\Jobs\SalsifyPrepareWebhook;
use App\Models\Salsify\Webhook;
use Illuminate\Http\Request;

class SalsifyController extends Controller
{

    public function apiStatus(Request $request)
    {
        return [
            'status' => 0,
            'message' => 'operational',
        ];
    }

    public function dummyWebhook(Request $request)
    {
        $webhook = new Webhook($request->all());
        $webhook->request_body = $request->getContent();
        $webhook->save();

        SalsifyPrepareWebhook::dispatch($webhook);

        return [
            'status' => 0,
            'msg' => 'ok',
            'data' => $webhook->toArray(),
        ];
    }

    public function channelPublished(Request $request)
    {
        $webhook = new Webhook($request->all());
        $webhook->request_body = $request->getContent();
        $webhook->save();

        SalsifyPrepareWebhook::dispatchAfterResponse($webhook);

        return [
            'status' => 0,
            'msg' => 'ok',
            'data' => $webhook->toArray(),
        ];
    }

    //
    public function incomingSalsifyWebhookProductsAdded(Request $request)
    {
        $webhook = new Webhook($request->json());

        $webhook->save();

        SalsifyPrepareWebhook::dispatchAfterResponse($webhook);
    }

    public function incomingSalsifyWebhookPropertiesChanged(Request $request)
    {
        $webhook = new Webhook($request->json());

        $webhook->save();

        SalsifyPrepareWebhook::dispatchAfterResponse($webhook);
    }

    public function incomingSalsifyWebhookProductsDeleted(Request $request)
    {
        $webhook = new Webhook($request->json());

        $webhook->save();

        SalsifyPrepareWebhook::dispatchAfterResponse($webhook);
    }

}

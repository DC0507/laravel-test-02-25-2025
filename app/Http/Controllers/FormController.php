<?php

namespace App\Http\Controllers;

use App\Mail\ContactSubmission;
use App\Mail\SubscribeSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

class FormController extends Controller
{
    //
    public function contactform(Request $request)
    {

        try
        {
            Log::debug("new contactform submission");

            $recip = collect(explode(';', config('form.contactform.recipient')));

            if(empty($recip))
            {
                throw new \Exception("no recipient email configured");
            }

            // get validation rules
            $rules = config('form.contactform.validation', []);

            // filter empty rule sets
            $rules = array_filter($rules);

            // make the validator object, give it request data
            $validator = Validator::make($request->all(), $rules);

            // send response of failed fields
            if($validator->fails())
            {
                return response([
                    'status' => 1,
                    'errors' => $validator->errors(),
                ], 422)->header('Content-Type', 'application/json');
            }

            // get validated data
            $data = $validator->validated();

            // no need to send email twice
            unset($data['email2']);

            // send the message
            $children = Arr::pull($data, 'ages-children', []);

            $map = [
                '0-3',
                '4-6',
                '7-9',
                '10-12',
                '13-17',
                '18+'
            ];

            foreach($map as $value)
            {
                Arr::set($data, $value, in_array($value, $children) ? 'Y' : 'N');
            }

            $map = [
                'receive-coupons',
                'receive-promotions',
                'receive-recipes',
                'receive-nutritional-news',
                'receive-other',
            ];

            foreach($map as $field)
            {
                $data[$field] = Arr::get($data, $field, 0) ? 'Y' : 'N';
            }

            $out = [];
            $astute_map = config('form.contactform.astute_mapping', []);
            foreach($astute_map as $our_key => $their_key)
            {
                $out[$their_key] = $data[$our_key];
            }

            Log::debug("contact payload: " . json_encode($out, JSON_PRETTY_PRINT));

            Mail::to($recip)->send(new ContactSubmission(Arr::dot($out)));
            Log::debug("sending contact submission to {$recip}");

            if($data['opt-in'])
            {
                $this->subscribeUser($data);
            }

            return response([
                'status' => 0,
                'message' => "Success"
            ], 200)->header('Content-Type', 'application/json');
        }
        catch (ValidationException $exception)
        {
            Log::debug($exception->getMessage());
            return response([
                'status' => 1,
                'message' => $exception->getMessage(),
                'errors' => $validator ? $validator->errors() : [],
            ], 422)->header('Content-Type', 'application/json');
        }
        catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
            return response([
                'status' => 1,
                'message' => $exception->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }

    }

    public function subscribeform(Request $request)
    {
        try
        {
            Log::debug("new subscribeform submission");

            // get validation rules
            $rules = config('form.subscribeform.validation', []);

            // filter empty rule sets
            $rules = array_filter($rules);

            // make the validator object, give it request data
            $validator = Validator::make($request->all(), $rules);

            // send response of failed fields
            if($validator->fails())
            {
                Log::debug("failed validation: " . json_encode($validator->errors(), JSON_PRETTY_PRINT));
                return response([
                    'status' => 1,
                    'errors' => $validator->errors(),
                ], 422)->header('Content-Type', 'application/json');
            }

            // get validated data
            $data = $validator->validated();

            // send the message
            $this->subscribeUser($data);

            Log::debug("successful contactform submission");
            return response([
                'status' => 0,
                'message' => "Success"
            ], 200)->header('Content-Type', 'application/json');
        }
        catch (ValidationException $exception)
        {
            Log::debug($exception->getMessage());
            return response([
                'status' => 1,
                'message' => $exception->getMessage(),
                'errors' => $validator ? $validator->errors() : [],
            ], 422)->header('Content-Type', 'application/json');
        }
        catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
            return response([
                'status' => 1,
                'message' => $exception->getMessage(),
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    protected function subscribeUser($data)
    {
        $recips = collect(explode(';', config('form.subscribeform.recipient')));
        if(empty($recips))
        {
            throw new \Exception("no recipient email configured");
        }
        foreach($recips as $recip)
        {
            Mail::to($recip)->send(new SubscribeSubmission($data));
            Log::debug("subscribed user: {$data['email']}");
        }

    }
}

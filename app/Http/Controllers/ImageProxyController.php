<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ImageProxyController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function proxyImage(Request $request): Application|Response|ResponseFactory
    {
        $encodedUrl = urldecode($request->input('imageUrl'));

        $response = Http::withHeaders([
            'Referer' => 'https://anhoch.com',
            'Accept' => 'image/*',
        ])->get($encodedUrl);

        return response($response->body(), 200)
            ->header('Content-Type', $response->header('Content-Type'))
            ->header('Access-Control-Allow-Origin', '*');
    }
}

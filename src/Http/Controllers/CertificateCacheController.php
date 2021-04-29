<?php namespace Braceyourself\NovaCertbot\Http\Controllers;

use Braceyourself\NovaCertbot\Certbot;
use Braceyourself\NovaCertbot\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CertificateCacheController extends BaseController
{
    private Certbot $client;

    public function __construct(Certbot $client)
    {
        $this->client = $client;
    }

    public function destroy(Request $request, $command)
    {
        \Cache::forget($key = "certbot $command");
        return response([
            'message' => "Cache cleared at key $key"
        ]);
    }

    public function store(Request $request)
    {
        return response([
            'message'      => 'cache updated',
            'certificates' => $this->client->certificates()
        ]);
    }
}

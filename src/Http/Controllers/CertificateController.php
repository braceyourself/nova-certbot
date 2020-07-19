<?php namespace Braceyourself\NovaCertbot\Http\Controllers;

use Braceyourself\NovaCertbot\Certbot;
use Braceyourself\NovaCertbot\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CertificateController extends BaseController
{
    private Certbot $client;

    public function __construct(Certbot $client)
    {
        $this->client = $client;
    }

    public function index(Request $request)
    {
        $parts = explode('|', $request->sort);
        $direction = \Arr::get($parts, 1);
        $column = \Arr::get($parts, 0);
        $sortMethod = $direction === 'desc' ? 'sortByDesc' : 'sortBy';

        return response([
            'data' => $this->client->certificates()
                ->$sortMethod(function ($i) use ($column) {
                    return $i->$column;
                })->values(),
            'cached_at'
        ]);
    }

    public function show(Request $request, $certificate)
    {
        $cert = $this->find($certificate);

        return $cert ? response($cert) : response(["Certificate Not found"], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'domains'   => 'array|min:1',
            'domains.*' => 'min:1',
            'dry_run'   => 'bool'
        ], [
            'min'           => 'This field requires at least :min value.',
            'domains.*.min' => 'This domain cannot be empty'
        ]);

        return $this->client->create($request->all());
    }

    public function update(Request $request, $certificate)
    {
        $cert = $this->find($certificate);

        if (!$cert) {
            return response(['Certificate Not Found']);
        }

        return response([
            'output' => $cert->renew($request->query('dry_run'))
        ]);
    }

    public function destroy(Request $request, $certificate)
    {
        $cert = $this->find($certificate);

        if (!$cert) {
            return response(['Certificate not found']);
        }

        if ($request->query('revoke')) {
            return $cert->revoke($request->query('dry_run'));
        }

        return response([
            'output' => $cert->delete($request->query('dry_run'))
        ]);
    }

    /*******************************************************
     * @internal
     ******************************************************/
    /**
     * @param $certificate
     * @return Certificate|null
     */
    private function find($certificate)
    {
        return $this->client->certificates()
            ->filter(function (Certificate $cert) use ($certificate) {
                return $cert->name == $certificate;
            })->first();
    }

}

<?php namespace Braceyourself\NovaCertbot;

use Braceyourself\NovaCertbot\Models\Certificate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Psr\Log\LoggerInterface;

class Certbot
{
    private SSH2 $connection;
    /**
     * @var RSA
     */
    private RSA $key;

    public function __construct(Collection $config)
    {
        $this->connection = new SSH2($config->get('server'));
//        $this->connection->

        $this->key = new RSA();
        $this->key->loadKey(file_get_contents($config->get('server_key')));
        if (!$this->connection->login($config->get('server_user'), $this->key)) {
            throw new \Exception("Could not login using the given credentials");
        }

    }

    /**
     * @param $command
     * @param int $cache_for
     * @param bool $dry_run
     * @return Stringable
     */
    private function run($command, $cache_for = 0, $dry_run = false)
    {
        $command = "/usr/local/bin/certbot $command -n";

        if (!($command = \Str::of($command))->contains(['certonly', 'renew']) && $dry_run) {
            return $command
                ->prepend("*** Skipping command because 'dry-run' flag is set ***\r\n")
                ->append("*** This command was not run! ***");
        }

        $output = \Cache::remember("$command", $cache_for, function () use ($command) {
            $this->connection->enablePTY();
            $this->connection->exec("$command");

            return \Str::of($this->connection->read());
        });

        return $output->prepend("Running Command: '$command'\r\n");
    }

    /**
     * @return Collection
     */
    public function certificates()
    {
        return $this->run('certificates', now()->addDay())
            ->explode('Found the following certs:')->mapInto(Stringable::class)->last()
            ->explode("Certificate Name:")->mapInto(Stringable::class)
            ->filter(fn(Stringable $str) => !empty((string)$str->trim()))
            ->map(fn($output) => new Certificate($output, $this));
    }

    public function create(array $attributes)
    {
        $domains = collect(\Arr::get($attributes, 'domains'))->join(',');
        $dry_run = \Arr::get($attributes, 'dry_run', false);

        $command = "certonly";
        if ($dry_run) {
            $command .= ' --dry-run';
        }
        $command .= " -d $domains";


        $output = $this->run($command)->explode("\r\n");



        if (!$dry_run) {
            $this->refreshCachedCertificates();
        }

        return $output;
    }

    public function renew($certificate_name, bool $dry_run = false)
    {
        $dry_run = $dry_run ? '--dry-run' : '';

        $this->refreshCachedCertificates();

        return $this->run("renew $dry_run --cert-name $certificate_name")
            ->explode("\r\n");
    }

    public function revoke($certificate_name, bool $dry_run = false)
    {
        $this->refreshCachedCertificates();

        return $this->run("revoke --cert-name $certificate_name")
            ->explode("\r\n");
    }

    public function delete($certificate_name, bool $dry_run = false)
    {
        $this->refreshCachedCertificates();

        return $this->run("delete --cert-name $certificate_name", 0, $dry_run)
            ->explode("\r\n");
    }

    private function refreshCachedCertificates()
    {
        dispatch(function () {
            \Cache::forget('certbot certificates');
//            $this->run('certificates', now()->addDay());
        });
    }
}

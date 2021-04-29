<?php namespace Braceyourself\NovaCertbot\Models;

use Braceyourself\NovaCertbot\Certbot;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

/**
 * @property array $domains
 * @property string $certificate_name
 * @property string $name
 * @property string $api_path
 * @property string $frontend_path
 * @property string $parsed_output
 *
 * Class Certificate
 * @package Braceyourself\NovaCertbot\Models
 */
class Certificate implements Arrayable
{
    use HasAttributes;

    private Certbot $certbot;

    protected function getArrayableAppends()
    {
        return [
            'expires_in',
            'api_path',
            'frontend_path',
        ];
    }

    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Domain constructor.
     * @param string $certbot_listing_output
     * @param Certbot $connection
     */
    public function __construct(string $certbot_listing_output, Certbot $connection)
    {
        $this->parseOutput($certbot_listing_output)
            ->each(fn($value, $key) => $this->$key = $value);

        $this->certbot = $connection;
    }

    /*******************************************************
     * attributes
     ******************************************************/
    /**
     * Accessor for api_path
     **/
    public function getApiPathAttribute()
    {
        return "/nova-vendor/braceyourself/certbot/certificates/$this->name";
    }

    /**
     * Accessor for frontend_path
     **/
    public function getFrontendPathAttribute()
    {
        return "/NovaCertbot/certificates/$this->name";
    }


    /**
     * Accessor for name
     **/
    public function getNameAttribute()
    {
        return $this->certificate_name;
    }


    /**
     * Accessor for expires_in
     **/
    public function getExpiresInAttribute()
    {
        return Carbon::parse($this->expires_on)->diffInDays(now());
    }


    /**
     * Muattor for domains
     * @param $value
     */
    public function setDomainsAttribute($value)
    {
        if (is_string($value)) {
            $value = \Str::of($value)->explode(' ');
        }

        $this->attributes['domains'] = $value->toArray();
    }

    /**
     * Muattor for expiry_date
     * @param $value
     */
    public function setExpiryDateAttribute($value)
    {
        $this->attributes['expires_on'] = $value;
    }


    /**
     * Accessor for expires_on
     **/
    public function getExpiresOnAttribute()
    {
        return $this->attributes['expires_on'];
    }


    public function parseOutput($output_string)
    {
        return \Str::of($output_string)->explode("\r\n")
            ->filter(fn($str) => !\Str::of($str)->startsWith('-'))
            ->mapWithKeys(function ($str) {
                $str = \Str::of($str)->trim();

                if (empty("$str")) {
                    return [];
                }

                $key = $str->match("/^([\w\s]*):\s/i");
                $value = $str->match("/^[\w\s]*:\s(.*)$/i");


                if ($key->isEmpty() && $str->isNotEmpty()) {
                    $key = 'certificate_name';
                    $value = "$str";
                }

                if (($valid_for = $str->match("/(\(valid:.*\))/i"))->isNotEmpty()) {
                    $value = $value->replace("$valid_for", '')->trim();
                }

                $key = (string)\Str::of($key)->snake();

                return ["$key" => "$value"];
            });
    }

    public function renew($dry_run = false)
    {
        return $this->certbot->renew($this->certificate_name, $dry_run);
    }

    public function delete($dry_run = false)
    {
        return $this->certbot->delete($this->certificate_name, $dry_run);
    }

    public function revoke($dry_run = false)
    {
        return $this->certbot->revoke($this->certificate_name, $dry_run);
    }

    public function toArray()
    {
        return $this->attributesToArray();
    }

    public function getDates()
    {
        return [];
    }

    protected function isDateAttribute($key)
    {
        return false;
    }

    public function getCasts()
    {
        return [];
    }

    public function getVisible()
    {
        return [];
    }

    public function getHidden()
    {
        return [];
    }

    public function getRelationValue($key)
    {
        return null;
    }
}

<?php

namespace App\Models\Salsify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'salsify_assets';

    protected $fillable = [
        'salsify_id',
        'source_url',
        'url',
        'name',
        'format',
        'etag',
        'resource_type',
        'filename',
        'system_id',
        'width',
        'height',
        'bytes',
    ];

    protected static $record_map = [
        'salsify:id' => 'salsify_id',
        'salsify:source_url' => 'source_url',
        'salsify:url' => 'url',
        'salsify:name' => 'name',
        'salsify:format' => 'format',
        'salsify:etag' => 'etag',
        'salsify:resource_type' => 'resource_type',
        'salsify:filename' => 'filename',
        'salsify:system_id' => 'system_id',
        'salsify:asset_width' => 'width',
        'salsify:asset_height' => 'height',
        'salsify:bytes' => 'bytes',
    ];

    protected static $int_fields = [
        'salsify:asset_width',
        'salsify:asset_height',
        'salsify:bytes',
    ];

    public static function prepareRecord($record)
    {
        $ret = [];
        foreach(self::$int_fields as $k)
        {
            $record[$k] = array_key_exists($k, $record) ? intval($record[$k]) : 0;
        }

        foreach(self::$record_map as $their_key => $our_key)
        {
            $ret[$our_key] = array_key_exists($their_key, $record) ? $record[$their_key] : "";
        }

        return $ret;
    }

    public function getCdnUrl()
    {
        $parts = explode('/', parse_url($this->url, PHP_URL_PATH));
        $path = [];
        $path[] = array_pop($parts);
        $path[] = array_pop($parts);
        return sprintf('%s/%s', env('ASSETS_CDN_HOST', 'https://img.web.welchs.com'), implode('/', array_reverse($path)));
    }

}

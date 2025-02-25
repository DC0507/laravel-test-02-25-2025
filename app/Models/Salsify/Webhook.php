<?php

namespace App\Models\Salsify;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

/*
{
  "id": 123456,
  "status": "completed",
  "started_at": "2016-01-21T13:42:41.336Z",
  "ended_at": "2016-01-21T13:42:48.916Z",
  "product_export_url": "https://salsify.s3-external-1.amazonaws.com/export.json",
  "digital_asset_file_export_url": null,
  "product_export_stats": null,
  "export_status": "completed",
  "delivery_status": null,
  "external_processing_status": null,
  "notification_status": "completed",
  "progress": {
    "stages": [
      "export",
      "deliver",
      "external_processing",
      "notify"
    ],
    "stage": null,
    "stage_time_remaining": 0,
    "stage_status": "completed"
  }
}
*/


    /**
     * Custom table name for this model.
     *
     * @var string
     */
    protected $table = 'webhook';

    /**
     * Unprepared status
     */
    const PREPARED_STATUS_UNPREPARED = 1;

    /**
     * Prepared status value
     */
    const PREPARED_STATUS_PREPARED = 2;

    /**
     * Set when payload file is fully processed.
     */
    const PREPARED_STATUS_COMPLETE = 4;

    /**
     * Failed status value
     */
    const PREPARED_STATUS_FAILED = 8;

    /**
     * The fillable properties.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'started_at',
        'ended_at',
        'product_feed_export_url',
        'digital_asset_file_export_url',
        'product_export_stats',
        'export_status',
        'delivery_status',
        'external_processing_status',
        'notification_status',
//        'progress',

        'request_body',
        'prepared_status',
        'downloaded_payload_filename',
    ];


//    public $status;
//    public $started_at;
//    public $ended_at;

    /**
     * @var string URI to the payload of json data
     */
//    public $product_export_url = '';
//
//    public $digital_asset_file_export_url;
//    public $product_export_stats;
//    public $export_status;
//    public $delivery_status;
//    public $external_processing_status;
//    public $notification_status;

//    public $progress = [
//        'stages' => [],
//        'stage' => null,
//        'stage_time_remaining' => 0,
//        'stage_status' => null,
//    ];

    /**
     * Our custom fields
     */

    /**
     * Original request body
     *
     * @var string
     */
//    public $request_body;

    /**
     * @var int Status of the webhook request
     */
//    public $prepared_status = self::PREPARED_STATUS_UNPREPARED;
    protected $attributes = [
        'prepared_status' => self::PREPARED_STATUS_UNPREPARED
    ];

    /**
     * Full path to the downloaded payload file.
     *
     * @var string
     * @return string
     */
//    public $downloaded_payload_filename = '';

    /**
     * Get string version of status.
     *
     * @return string
     */
    public function getStatusString()
    {
        switch(true)
        {
            case $this->isUnprepared() : return 'Unprepared';
            case $this->isPrepared() : return 'Prepared';
            case $this->isFailed() : return 'Failed';
            case $this->isComplete() : return 'Complete';
            default : return 'Unknown';
        }
    }

    /**
     * Did the download fail?
     *
     * @return bool
     */
    public function isFailed()
    {
        return $this->prepared_status == self::PREPARED_STATUS_FAILED;
    }

    /**
     * Is the download complete and on disk.
     *
     * @return bool
     */
    public function isPrepared()
    {
        return $this->prepared_status == self::PREPARED_STATUS_PREPARED;
    }

    /**
     * Have not attempted to fetch the payload file.
     *
     * @return bool
     */
    public function isUnprepared()
    {
        return $this->prepared_status == self::PREPARED_STATUS_UNPREPARED;
    }

    /**
     * Is it complete?
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->prepared_status == self::PREPARED_STATUS_COMPLETE;
    }

    /**
     * Mark it complete.
     * @return $this
     */
    public function markComplete()
    {
        $this->prepared_status = self::PREPARED_STATUS_COMPLETE;
        return $this;
    }

    /**
     * Mark this webhook failed.
     *
     * @return $this
     */
    public function markFailed()
    {
        $this->prepared_status = self::PREPARED_STATUS_FAILED;
        return $this;
    }

    /**
     * Mark this webhook prepared.
     *
     * @return $this
     */
    public function markPrepared()
    {
        $this->prepared_status = self::PREPARED_STATUS_PREPARED;
        return $this;
    }

    /**
     * Mark the webhook unprepared.
     *
     * @return $this
     */
    public function markUnprepared()
    {
        $this->prepared_status = self::PREPARED_STATUS_UNPREPARED;
        return $this;
    }

    /**
     * Compute what the local filename should be.
     *
     * @return string
     */
    public function computeLocalFilename()
    {
        $path = parse_url($this->product_feed_export_url, PHP_URL_PATH);
        $basename = basename($path);

        return "payloads/" . $this->id . "_" . $basename . '_' . microtime(true) . '.json';
    }


}

<?php

namespace RedSquirrelStudio\LaravelBackpackExportOperation\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportLog extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'export_log';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id', 'file_path', 'file_type', 'disk', 'model', 'export_parameters',
        'config', 'started_at', 'completed_at'
    ];
    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'export_parameters' => 'array'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('backpack.base.user_model_fqn') ?? 'App\Models\User', 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * @return int
     */
    public function getDurationAttribute(): int
    {
        return Carbon::parse($this->started_at)->diffInSeconds($this->completed_at);
    }

    /**
     * @return string|null
     */
    public function getFileUrlAttribute(): ?string
    {
        if (Storage::disk($this->disk)->exists($this->file_path)) {
            $url = Storage::disk($this->disk)->url($this->file_path);
            return str_contains('http', $url) ? $url : null;
        }
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}

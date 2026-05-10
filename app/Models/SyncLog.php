<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * SyncLog — Records every action taken during an Atlas sync.
 *
 * Stored in the local MongoDB 'sync_logs' collection ONLY.
 * Never synced to Atlas.
 */
class SyncLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sync_logs';

    protected $fillable = [
        'session_id',      // Unique ID per sync run
        'collection',      // Which collection this log belongs to
        'document_id',     // The _id of the document
        'action',          // insert | update | skip | conflict | failed | backup
        'reason',          // Human-readable explanation
        'local_version',   // sync_version on local doc
        'local_updated_at',
        'atlas_updated_at',
        'dry_run',         // bool — was this a dry run?
        'error',           // Exception message if failed
        'duration_ms',     // How long the operation took
    ];

    /**
     * Log a sync action for a single document.
     */
    public static function record(
        string $sessionId,
        string $collection,
        string $documentId,
        string $action,
        string $reason = '',
        array  $meta   = [],
        bool   $dryRun = false,
    ): void {
        static::create([
            'session_id'      => $sessionId,
            'collection'      => $collection,
            'document_id'     => $documentId,
            'action'          => $action,
            'reason'          => $reason,
            'local_version'   => $meta['local_version']   ?? null,
            'local_updated_at'=> $meta['local_updated_at'] ?? null,
            'atlas_updated_at'=> $meta['atlas_updated_at'] ?? null,
            'dry_run'         => $dryRun,
            'error'           => $meta['error']           ?? null,
            'duration_ms'     => $meta['duration_ms']     ?? null,
        ]);
    }
}

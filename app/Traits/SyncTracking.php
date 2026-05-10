<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

/**
 * SyncTracking Trait
 *
 * Add to any MongoDB Eloquent model to automatically track sync state.
 * Every local save stamps sync_status = 'pending' and local_updated_at.
 *
 * Fields added to every document:
 *   sync_status      : pending | synced | conflict | failed
 *   local_updated_at : timestamp of last local change
 *   atlas_updated_at : timestamp of last known Atlas state (set after sync)
 *   synced_at        : timestamp of last successful sync
 *   sync_version     : incremented on every local change (conflict detection)
 *   last_sync_error  : error message if sync failed, null otherwise
 */
trait SyncTracking
{
    public static function bootSyncTracking(): void
    {
        // Every time a document is saved locally, mark it pending
        static::saving(function ($model) {
            $now = Carbon::now()->toISOString();

            // Only stamp if not already being set by the sync command itself
            if (! $model->getOption('_sync_command_write')) {
                $model->sync_status     = 'pending';
                $model->local_updated_at = $now;
                $model->sync_version    = ($model->sync_version ?? 0) + 1;
            }
        });
    }

    /**
     * Used by the sync command to write without triggering the saving hook.
     * Model::withSyncWrite(fn() => $model->save())
     */
    public static function withSyncWrite(callable $callback): mixed
    {
        return $callback();
    }

    /**
     * Mark this document as synced after a successful Atlas push.
     */
    public function markSynced(string $atlasUpdatedAt): void
    {
        $this->withoutEvents(function () use ($atlasUpdatedAt) {
            $this->update([
                'sync_status'     => 'synced',
                'synced_at'       => Carbon::now()->toISOString(),
                'atlas_updated_at' => $atlasUpdatedAt,
                'last_sync_error' => null,
            ]);
        });
    }

    /**
     * Mark this document as having a conflict (both sides changed).
     */
    public function markConflict(string $reason): void
    {
        $this->withoutEvents(function () use ($reason) {
            $this->update([
                'sync_status'    => 'conflict',
                'last_sync_error' => $reason,
            ]);
        });
    }

    /**
     * Mark this document as failed (network or write error).
     */
    public function markFailed(string $error): void
    {
        $this->withoutEvents(function () use ($error) {
            $this->update([
                'sync_status'    => 'failed',
                'last_sync_error' => $error,
            ]);
        });
    }

    /**
     * Get sync metadata fields for $fillable.
     */
    public static function syncFillable(): array
    {
        return [
            'sync_status',
            'local_updated_at',
            'atlas_updated_at',
            'synced_at',
            'sync_version',
            'last_sync_error',
        ];
    }

    /**
     * Helper: get internal option (used to prevent save loop).
     */
    protected function getOption(string $key): mixed
    {
        return $this->_options[$key] ?? false;
    }
}

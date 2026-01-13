<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property string $disk
 * @property string $path
 * @property string $scope
 * @property \App\Models\User|null $user
 * @property \App\Models\Account $account
 * @property string $client_name
 */
class TemporaryUpload extends Model
{
    use HasUuid, Prunable;

    protected $guarded = false;

    protected static function booted(): void
    {
        static::deleting(function (TemporaryUpload $upload) {
            if (Storage::disk($upload->disk)->exists($upload->path)) {
                Storage::disk($upload->disk)->delete($upload->path);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<=', now()->subHours(24));
    }

    /**
     * Copy file to given disk and directory.
     */
    public function copyTo(string $disk, string $directory): string
    {
        $fileName = Str::random(20).'.'.File::extension($this->path);

        Storage::disk($disk)
            ->writeStream(
                path: $directory.DIRECTORY_SEPARATOR.$fileName,
                resource: Storage::disk($this->disk)->readStream($this->path),
            );

        return $directory.'/'.$fileName;
    }

    /**
     * Copy file to the separate workspace for processing.
     */
    public function copyToWorkspace(?string $name = null): string
    {
        $name = $name ?: Str::lower(Str::random(20));

        return $this->copyTo(disk: 'local', directory: 'workspaces'.DIRECTORY_SEPARATOR.$name);
    }

    /**
     * Get the file URL.
     */
    public function url(): ?string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the disk where to store the temporary file.
     */
    public static function disk(string $scope): string
    {
        return 'public';
    }

    /**
     * Get the directory where to store the temporary file.
     */
    public static function dir(string $scope): string
    {
        return 'tmp';
    }

    /**
     * List of available temporary file scopes.
     */
    public static function scopes(): array
    {
        return [
            'InvoiceLogo' => [
                'image',
                'max:8192',
                'dimensions:min_width=100,min_height=100,max_width=400,max_height=400',
                'extensions:jpg,png,jpeg',
                'mimes:jpg,png,jpeg',
            ],
            'InvoiceSignature' => [
                'image',
                'max:8192',
                'dimensions:min_width=100,min_height=100,max_width=400,max_height=400',
                'extensions:jpg,png,jpeg',
                'mimes:jpg,png,jpeg',
            ],
            'BankTransactionsCamt' => [
                'extensions:xml',
                'mimes:xml',
                'max:25600',
            ],
        ];
    }
}

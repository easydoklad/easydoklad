<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Models\Concerns\HasUuid;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

/**
 * @property int $id
 * @property \App\Enums\DocumentType $document_type
 * @property \App\Models\Account|null $account
 * @property array $options
 * @property bool $is_default
 * @property string $name
 * @property string $installation_path
 * @property string $package
 * @property string $description
 */
class DocumentTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentTemplateFactory> */
    use HasFactory, HasUuid;

    protected $guarded = false;

    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'is_default' => 'boolean',
            'options' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeOfType(Builder $builder, DocumentType $type): void
    {
        $builder->where('document_type', $type);
    }

    public function scopeAvailableForAccount(Builder $builder, Account $account): void
    {
        $builder->where(function (Builder $builder) use ($account) {
            $builder->whereNull('account_id')->orWhere('account_id', $account->id);
        });
    }

    /**
     * Get list of available locales.
     */
    public function getLocales(): array
    {
        return $this->options['locales'];
    }

    /**
     * Get a given locale if it is supported by the template. Otherwise, return a first available locale.
     */
    public function resolveLocale(string $locale): string
    {
        $locales = $this->getLocales();

        return in_array($locale, $locales) ? $locale : Arr::first($locales);
    }

    /**
     * Render template to PDF.
     */
    public function render(array $input): string
    {
        // TODO: Add safe execution environment support

        $node = config('app.node_path');

        $script = Storage::path($this->installation_path).'/'.$this->options['entryPoint'];

        if (! File::exists($script)) {
            throw new RuntimeException('Invalid template: the rendering script does not exist');
        }

        $data = base64_encode(json_encode($input));

        return Process::input($data)->run([$node, $script])->throw()->output();
    }

    /**
     * Install template from archive.
     */
    public static function installFromArchive(string $path, ?Account $account = null, bool $default = false): static
    {
        $tempDir = 'temp/unpack-'.Str::random('10');

        $archive = new ZipArchive;

        if ($archive->open($path) !== true) {
            throw new InvalidArgumentException("Unable to open archive [$path]");
        }

        if (! Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        Storage::makeDirectory($tempDir);

        $extractPath = Storage::path($tempDir);

        if ($archive->extractTo($extractPath) !== true) {
            $archive->close();

            throw new InvalidArgumentException("Unable to extract archive [$path]");
        }

        $archive->close();

        $template = static::installFromDirectory($extractPath, $account, $default);

        Storage::deleteDirectory($tempDir);

        return $template;
    }

    /**
     * Install unpacked template from a directory.
     */
    public static function installFromDirectory(string $directory, ?Account $account = null, bool $default = false): static
    {
        $src = Storage::build(['driver' => 'local', 'root' => $directory]);

        if (! $src->exists('manifest.json')) {
            throw new InvalidArgumentException('The folder does not contain a manifest file');
        }

        $manifest = json_decode($src->get('manifest.json'), true);

        $validator = Validator::make($manifest, [
            'name' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', Rule::enum(DocumentType::class)],
            'package' => ['required', 'string', 'max:191'],
            'entryPoint' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_-]+\.js\z/u', 'max:191', function (string $attribute, mixed $value, Closure $fail) use ($src) {
                if (! $src->exists($value)) {
                    $fail('The entry point file does not exist');
                }
            }],
            'locales' => ['required', 'array', 'min:1'],
            'locales.*' => ['string', 'min:2', 'max:2', 'distinct:ignore_case'],
        ]);

        if ($validator->fails()) {
            throw new InvalidArgumentException("The manifest is invalid: {$validator->errors()->first()}");
        }

        $name = $manifest['name'];
        $description = $manifest['description'];
        $type = DocumentType::from($manifest['type']);
        $package = $manifest['package'];
        $entryPoint = $manifest['entryPoint'];
        $locales = collect($manifest['locales'])->map(fn (string $locale) => Str::lower($locale))->all();

        $dest = config('app.template_installation_path');

        if (! $dest) {
            throw new RuntimeException('The app.template_installation_path is not configured');
        }

        if (! Storage::exists($dest)) {
            Storage::makeDirectory($dest);
        }

        if (
            $existingInstallation = static::query()
                ->where('document_type', $type)
                ->where('package', $package)
                ->when($account, fn (Builder $builder, Account $account) => $builder->whereBelongsTo($account))
                ->first()
        ) {
            $installationPath = $existingInstallation->installation_path;

            Storage::deleteDirectory($installationPath);
            Storage::makeDirectory($installationPath);

            Storage::writeStream($installationPath.'/manifest.json', $src->readStream('manifest.json'));
            Storage::writeStream($installationPath.'/'.$entryPoint, $src->readStream($entryPoint));

            $existingInstallation->update([
                'name' => $name,
                'description' => $description,
                'options' => [
                    'locales' => $locales,
                    'entryPoint' => $entryPoint,
                ],
            ]);

            return $existingInstallation;
        }

        $installationPath = $dest.'/'.hash('sha256', $package.($account ? ':'.$account->id : ''));

        if (Storage::exists($installationPath)) {
            throw new InvalidArgumentException('The template is already installed');
        }

        Storage::makeDirectory($installationPath);
        Storage::writeStream($installationPath.'/manifest.json', $src->readStream('manifest.json'));
        Storage::writeStream($installationPath.'/'.$entryPoint, $src->readStream($entryPoint));

        $template = new DocumentTemplate([
            'document_type' => $type,
            'name' => $name,
            'description' => $description,
            'package' => $package,
            'installation_path' => $installationPath,
            'options' => [
                'locales' => $locales,
                'entryPoint' => $entryPoint,
            ],
            'is_default' => $default,
        ]);
        $template->account()->associate($account);
        $template->save();

        return $template;
    }
}

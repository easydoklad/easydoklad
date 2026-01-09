<?php


namespace App\Http\Controllers;


use App\Facades\Accounts;
use App\Models\TemporaryUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemporaryUploadController
{
    public function store(Request $request)
    {
        $scopes = TemporaryUpload::scopes();

        $request->validate([
            'scope' => ['required', 'string', 'max:191', Rule::in(array_keys($scopes))],
        ]);

        $scope = $request->input('scope');

        $fileRules = ['required', 'file'];

        $scopeRules = $scopes[$scope];
        if (is_array($scopeRules)) {
            $fileRules = [...$fileRules, ...$scopeRules];
        }

        $request->validate(['file' => $fileRules]);

        $file = $request->file('file');
        $disk = TemporaryUpload::disk($scope);
        $dir = TemporaryUpload::dir($scope);

        $fileName = Str::random(20).'.'.$file->extension();

        if ($disk === 'public') {
            $file->storePubliclyAs($dir, $fileName, ['disk' => $disk]);
        } else {
            $file->storeAs($dir, $fileName, ['disk' => $disk]);
        }

        $upload = new TemporaryUpload([
            'disk' => $disk,
            'path' => $dir.DIRECTORY_SEPARATOR.$fileName,
            'scope' => $scope,
            'client_name' => $file->getClientOriginalName(),
        ]);

        $upload->user()->associate($request->user());
        $upload->account()->associate(Accounts::current());

        $upload->save();

        return response()->json([
            'id' => $upload->uuid,
            'url' => $upload->url(),
            'client_name' => $upload->client_name,
        ]);
    }
}

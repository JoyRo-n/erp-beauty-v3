<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EncryptedArray implements CastsAttributes
{
    /**
     * Decrypt and JSON-decode the array attribute when retrieved from the database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $str = (string) $value;

        try {
            $decrypted = Crypt::decryptString($str);
            $decoded = json_decode($decrypted, true);
            return is_array($decoded) ? $decoded : [];
        } catch (DecryptException) {
            $decoded = json_decode($str, true);
            return is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(';', $str)));
        }
    }

    /**
     * JSON-encode and encrypt the array attribute before storing it in the database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $arrayValue = is_array($value) ? $value : [$value];

        return Crypt::encryptString(json_encode(array_values($arrayValue)));
    }
}


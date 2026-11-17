<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EncryptedString implements CastsAttributes
{
    /**
     * Decrypt the attribute value when retrieved from the database.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (DecryptException) {
            return (string) $value;
        }
    }

    /**
     * Encrypt the attribute value before storing it in the database.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $str = (string) $value;

        // Avoid double-encrypting if already encrypted
        if (str_starts_with($str, 'eyJpdiI6')) {
            try {
                Crypt::decryptString($str);
                return $str;
            } catch (DecryptException) {
                // Not valid ciphertext, encrypt normally
            }
        }

        return Crypt::encryptString($str);
    }
}


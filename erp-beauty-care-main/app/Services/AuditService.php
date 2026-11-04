<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\LogNote;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(string $action, string $docType, string $docId, ?string $details = null, ?string $actor = null, ?string $role = null): AuditLog
    {
        $user = Auth::user();
        $actorName = $actor ?? ($user ? $user->name : 'System');
        $actorRole = $role ?? ($user ? $user->role : 'Admin');

        return AuditLog::create([
            'actor' => $actorName,
            'role' => $actorRole,
            'action' => $action,
            'document_type' => $docType,
            'document_id' => $docId,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    public static function addNote(string $docType, string $docId, string $message, ?string $author = null): LogNote
    {
        $user = Auth::user();
        $authorName = $author ?? ($user ? $user->name : 'System');

        return LogNote::create([
            'author' => $authorName,
            'document_type' => $docType,
            'document_id' => $docId,
            'message' => $message,
            'created_at' => now(),
        ]);
    }
}

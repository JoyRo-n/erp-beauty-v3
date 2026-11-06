<?php

namespace App\Http\Controllers;

use App\Models\AccessGrant;
use App\Models\AccessRequest;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RoleAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessController extends Controller
{
    /**
     * Resolve the active user (same rules as ErpController).
     */
    protected function activeUser(Request $request): User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        if (app()->environment('testing')) {
            $role = $request->session()->get('active_role', 'Sales');
            $user = User::where('role', $role)->first();
            if ($user) {
                Auth::login($user);
                return $user;
            }
        }

        abort(redirect()->route('login')->with('warning', 'Silakan masuk terlebih dahulu.'));
    }

    /**
     * Only Admin/Owner may manage cross-division access.
     */
    protected function requireSuper(User $user): void
    {
        if (!RoleAccessService::isSuperRole($user->role)) {
            AuditService::log('Access Denied', 'Access Management', 'ACCESS', "Role {$user->role} tried to manage cross-division access", $user->name, $user->role);
            abort(403, 'Access Denied: hanya Admin atau Owner yang dapat mengelola akses lintas divisi.');
        }
    }

    /**
     * Grant a division team access to one or more applications of other divisions.
     */
    public function grant(Request $request)
    {
        $user = $this->activeUser($request);
        $this->requireSuper($user);

        $allApps = RoleAccessService::getAllApps();
        $teams = array_diff(array_keys(RoleAccessService::getProfiles()), ['Admin', 'Owner']);

        $validated = $request->validate([
            'grantee_role' => 'required|in:' . implode(',', $teams),
            'apps' => 'required|array|min:1',
            'apps.*' => 'in:' . implode(',', array_keys($allApps)),
            'note' => 'nullable|string|max:255',
        ], [
            'apps.required' => 'Pilih minimal satu modul yang akan diberikan.',
        ]);

        $role = $validated['grantee_role'];
        $baseKeys = RoleAccessService::getBaseAppKeys($role);
        $added = [];

        DB::transaction(function () use ($validated, $role, $baseKeys, $user, $allApps, &$added) {
            foreach (array_unique($validated['apps']) as $appKey) {
                // Skip modules the team already owns or already has
                if (in_array($appKey, $baseKeys, true) || AccessGrant::where('grantee_role', $role)->where('app_key', $appKey)->exists()) {
                    continue;
                }

                AccessGrant::create([
                    'grantee_role' => $role,
                    'app_key' => $appKey,
                    'granted_by' => $user->name,
                    'note' => $validated['note'] ?? null,
                ]);
                $added[] = $appKey;

                $app = $allApps[$appKey];
                $noteText = !empty($validated['note']) ? " Catatan: {$validated['note']}" : '';

                // 1) Notify the team that received the access
                AppNotification::create([
                    'recipient_role' => $role,
                    'title' => "Akses baru: {$app['title']}",
                    'message' => "{$user->name} memberikan tim {$role} akses ke modul {$app['title']} ({$app['division']}).{$noteText}",
                    'link' => route($app['route']),
                ]);

                // 2) Notify the division(s) that own the module
                foreach (RoleAccessService::getOwningRoles($appKey) as $owner) {
                    if ($owner === $role) {
                        continue;
                    }
                    AppNotification::create([
                        'recipient_role' => $owner,
                        'title' => "Tim {$role} mengakses modul {$app['title']}",
                        'message' => "{$user->name} memberi tim {$role} akses ke modul {$app['title']} milik divisi Anda. Dokumen terkait kini dapat dilihat dan diproses oleh tim tersebut.{$noteText}",
                        'link' => route($app['route']),
                    ]);
                }

                AuditService::log('Access Granted', 'Access Management', $role . ':' . $appKey, "Granted {$role} access to {$app['title']}.{$noteText}", $user->name, $user->role);
            }
        });

        if (empty($added)) {
            return redirect()->route('s01.home')->with('warning', "Tidak ada akses baru: tim {$role} sudah memiliki modul yang dipilih.");
        }

        $titles = collect($added)->map(fn ($k) => $allApps[$k]['title'])->implode(', ');
        return redirect()->route('s01.home')->with('success', "Akses {$titles} berhasil diberikan ke tim {$role}. Notifikasi telah dikirim ke divisi terkait.");
    }

    /**
     * Revoke a previously granted cross-division access.
     */
    public function revoke(Request $request, int $id)
    {
        $user = $this->activeUser($request);
        $this->requireSuper($user);

        $grant = AccessGrant::find($id);
        if (!$grant) {
            return redirect()->route('s01.home')->with('error', 'Data akses tidak ditemukan.');
        }

        $allApps = RoleAccessService::getAllApps();
        $app = $allApps[$grant->app_key] ?? ['title' => $grant->app_key, 'route' => 's01.home'];

        AppNotification::create([
            'recipient_role' => $grant->grantee_role,
            'title' => "Akses dicabut: {$app['title']}",
            'message' => "{$user->name} mencabut akses tim {$grant->grantee_role} ke modul {$app['title']}.",
            'link' => route('s01.home'),
        ]);
        foreach (RoleAccessService::getOwningRoles($grant->app_key) as $owner) {
            if ($owner !== $grant->grantee_role) {
                AppNotification::create([
                    'recipient_role' => $owner,
                    'title' => "Akses tim {$grant->grantee_role} dicabut",
                    'message' => "Tim {$grant->grantee_role} tidak lagi memiliki akses ke modul {$app['title']}.",
                    'link' => route($app['route']),
                ]);
            }
        }

        AuditService::log('Access Revoked', 'Access Management', $grant->grantee_role . ':' . $grant->app_key, "Revoked {$grant->grantee_role} access to {$app['title']}", $user->name, $user->role);
        $grant->delete();

        return redirect()->route('s01.home')->with('success', "Akses {$app['title']} untuk tim {$grant->grantee_role} dicabut.");
    }

    /**
     * Only Master Admin ('Admin') may approve or reject forwarded staff access requests.
     */
    protected function requireMasterAdmin(User $user): void
    {
        if ($user->role !== 'Admin') {
            AuditService::log('Access Denied', 'Access Request Approval', 'ACCESS-REQ', "Role {$user->role} tried to approve/reject staff access request", $user->name, $user->role);
            abort(403, 'Access Denied: hanya Master Admin yang dapat menyetujui atau menolak permintaan akses staf.');
        }
    }

    /**
     * Staff forwards a cross-division module access request to Master Admin.
     */
    public function submitRequest(Request $request)
    {
        $user = $this->activeUser($request);

        if (RoleAccessService::isSuperRole($user->role)) {
            return redirect()->back()->with('info', 'Akun Admin / Owner sudah memiliki akses penuh ke seluruh divisi.');
        }

        $allApps = RoleAccessService::getAllApps();
        $validated = $request->validate([
            'app_key' => 'required|in:' . implode(',', array_keys($allApps)),
            'reason' => 'required|string|max:300',
        ], [
            'app_key.required' => 'Pilih modul divisi yang ingin diajukan.',
            'reason.required' => 'Tuliskan alasan permintaan akses untuk Master Admin.',
        ]);

        $role = $user->role;
        $appKey = $validated['app_key'];
        $app = $allApps[$appKey];

        if (in_array($appKey, RoleAccessService::getBaseAppKeys($role), true)
            || AccessGrant::where('grantee_role', $role)->where('app_key', $appKey)->exists()) {
            return redirect()->back()
                ->with('open_access_drawer', true)
                ->with('warning', "Tim {$role} sudah memiliki akses aktif ke modul {$app['title']}.");
        }

        if (AccessRequest::where('requester_role', $role)->where('app_key', $appKey)->where('status', 'Pending')->exists()) {
            return redirect()->back()
                ->with('open_access_drawer', true)
                ->with('warning', "Permintaan akses ke modul {$app['title']} sudah diajukan dan sedang menunggu persetujuan Master Admin.");
        }

        DB::transaction(function () use ($user, $role, $appKey, $app, $validated) {
            $req = AccessRequest::create([
                'requester_role' => $role,
                'requester_name' => $user->name,
                'app_key' => $appKey,
                'reason' => $validated['reason'],
                'status' => 'Pending',
            ]);

            AppNotification::create([
                'recipient_role' => 'Admin',
                'title' => "Permintaan akses: {$role} → {$app['title']}",
                'message' => "{$user->name} ({$role}) meminta akses ke modul {$app['title']} ({$app['division']}). Alasan: {$validated['reason']}",
                'link' => route('s01.home', ['open_access_drawer' => 1]),
            ]);

            AuditService::log('Access Requested', 'Access Request', (string) $req->id, "{$user->name} ({$role}) requested {$app['title']}: {$validated['reason']}", $user->name, $role);
        });

        return redirect()->back()
            ->with('open_access_drawer', true)
            ->with('success', "Permintaan akses modul {$app['title']} telah diteruskan ke Master Admin dan menunggu persetujuan.");
    }

    /**
     * Master Admin approves a pending staff access request and activates the AccessGrant.
     */
    public function approveRequest(Request $request, int $id)
    {
        $user = $this->activeUser($request);
        $this->requireMasterAdmin($user);

        $accessReq = AccessRequest::find($id);
        if (!$accessReq || $accessReq->status !== 'Pending') {
            return redirect()->back()
                ->with('open_access_drawer', true)
                ->with('warning', 'Permintaan akses tidak ditemukan atau sudah diproses.');
        }

        $validated = $request->validate([
            'review_note' => 'nullable|string|max:255',
        ]);

        $allApps = RoleAccessService::getAllApps();
        $app = $allApps[$accessReq->app_key] ?? ['title' => $accessReq->app_key, 'division' => '-', 'route' => 's01.home'];
        $role = $accessReq->requester_role;
        $noteForGrant = !empty($validated['review_note'])
            ? "{$accessReq->reason} (Catatan Admin: {$validated['review_note']})"
            : $accessReq->reason;

        DB::transaction(function () use ($accessReq, $user, $validated, $role, $app, $noteForGrant) {
            $accessReq->update([
                'status' => 'Approved',
                'reviewed_by' => $user->name,
                'review_note' => $validated['review_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            AccessGrant::firstOrCreate(
                ['grantee_role' => $role, 'app_key' => $accessReq->app_key],
                ['granted_by' => $user->name, 'note' => $noteForGrant]
            );

            $adminNoteText = !empty($validated['review_note']) ? " Catatan Master Admin: {$validated['review_note']}" : '';

            // Notify requesting staff division
            AppNotification::create([
                'recipient_role' => $role,
                'title' => "Permintaan disetujui: {$app['title']}",
                'message' => "Master Admin ({$user->name}) menyetujui permintaan akses tim {$role} ke modul {$app['title']}.{$adminNoteText}",
                'link' => route($app['route']),
            ]);

            // Notify owning division(s)
            foreach (RoleAccessService::getOwningRoles($accessReq->app_key) as $owner) {
                if ($owner !== $role) {
                    AppNotification::create([
                        'recipient_role' => $owner,
                        'title' => "Tim {$role} mengakses modul {$app['title']}",
                        'message' => "Master Admin menyetujui permintaan akses sementara tim {$role} ke modul {$app['title']} divisi Anda (Alasan: {$accessReq->reason}).",
                        'link' => route($app['route']),
                    ]);
                }
            }

            AuditService::log('Access Request Approved', 'Access Request', (string) $accessReq->id, "Master Admin approved {$role} access to {$app['title']}", $user->name, $user->role);
        });

        return redirect()->back()
            ->with('open_access_drawer', true)
            ->with('success', "Permintaan akses modul {$app['title']} untuk tim {$role} disetujui dan langsung aktif.");
    }

    /**
     * Master Admin rejects a pending staff access request.
     */
    public function rejectRequest(Request $request, int $id)
    {
        $user = $this->activeUser($request);
        $this->requireMasterAdmin($user);

        $accessReq = AccessRequest::find($id);
        if (!$accessReq || $accessReq->status !== 'Pending') {
            return redirect()->back()
                ->with('open_access_drawer', true)
                ->with('warning', 'Permintaan akses tidak ditemukan atau sudah diproses.');
        }

        $validated = $request->validate([
            'review_note' => 'nullable|string|max:255',
        ]);

        $allApps = RoleAccessService::getAllApps();
        $app = $allApps[$accessReq->app_key] ?? ['title' => $accessReq->app_key, 'route' => 's01.home'];
        $role = $accessReq->requester_role;

        DB::transaction(function () use ($accessReq, $user, $validated, $role, $app) {
            $accessReq->update([
                'status' => 'Rejected',
                'reviewed_by' => $user->name,
                'review_note' => $validated['review_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            $adminNoteText = !empty($validated['review_note']) ? " Alasan: {$validated['review_note']}" : '';

            AppNotification::create([
                'recipient_role' => $role,
                'title' => "Permintaan ditolak: {$app['title']}",
                'message' => "Master Admin ({$user->name}) menolak permintaan akses tim {$role} ke modul {$app['title']}.{$adminNoteText}",
                'link' => route('s01.home', ['open_access_drawer' => 1]),
            ]);

            AuditService::log('Access Request Rejected', 'Access Request', (string) $accessReq->id, "Master Admin rejected {$role} request for {$app['title']}", $user->name, $user->role);
        });

        return redirect()->back()
            ->with('open_access_drawer', true)
            ->with('info', "Permintaan akses modul {$app['title']} dari tim {$role} telah ditolak.");
    }

    /**
     * Staff cancels their own pending access request before Master Admin reviews it.
     */
    public function cancelRequest(Request $request, int $id)
    {
        $user = $this->activeUser($request);
        $accessReq = AccessRequest::find($id);

        if (!$accessReq || $accessReq->status !== 'Pending' || $accessReq->requester_role !== $user->role) {
            return redirect()->back()
                ->with('open_access_drawer', true)
                ->with('warning', 'Permintaan akses tidak dapat dibatalkan.');
        }

        AuditService::log('Access Request Cancelled', 'Access Request', (string) $accessReq->id, "{$user->name} ({$user->role}) cancelled pending request for {$accessReq->app_key}", $user->name, $user->role);
        $accessReq->delete();

        return redirect()->back()
            ->with('open_access_drawer', true)
            ->with('info', 'Permintaan akses berhasil dibatalkan.');
    }

    /**
     * Mark all notifications of the current team as read.
     */
    public function markNotificationsRead(Request $request)
    {
        $user = $this->activeUser($request);

        AppNotification::where('recipient_role', $user->role)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->back();
    }
}


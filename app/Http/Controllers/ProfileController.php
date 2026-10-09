<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\R2StorageService;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($request->only(['full_name', 'username', 'email', 'phone']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Memperbarui foto profil / avatar user ke Cloudflare R2.
     */
    public function updateAvatar(Request $request, R2StorageService $r2Storage): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->avatar_url) {
            try {
                $r2Storage->delete($user->avatar_url, R2StorageService::DISK_PUBLIC);
            } catch (\Exception $e) {
                Log::error('Gagal menghapus avatar lama di R2: ' . $e->getMessage());
            }
        }

        $avatarUrl = $r2Storage->upload($request->file('avatar'), 'avatars', R2StorageService::DISK_PUBLIC);

        $user->update([
            'avatar_url' => $avatarUrl,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Menghapus foto profil / avatar user.
     */
    public function destroyAvatar(Request $request, R2StorageService $r2Storage): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user->avatar_url) {
            try {
                $r2Storage->delete($user->avatar_url, R2StorageService::DISK_PUBLIC);
            } catch (\Exception $e) {
                Log::error('Gagal menghapus file avatar di R2: ' . $e->getMessage());
            }

            $user->update([
                'avatar_url' => null,
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Foto profil berhasil dihapus.');
    }
}
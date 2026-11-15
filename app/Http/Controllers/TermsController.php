<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermsController extends Controller
{
    public function accept(Request $request)
    {
        $validated = $request->validate([
            'document' => ['required', 'in:customer_tos,designer_tos'],
            'setuju'   => ['required', 'accepted'],
        ], [
            'setuju.accepted' => 'Kamu harus menyetujui ketentuan untuk melanjutkan.',
        ]);

        $user = $request->user();

        if ($validated['document'] === 'designer_tos' && $user->role !== 'designer') {
            abort(403);
        }
        if ($validated['document'] === 'customer_tos' && $user->role !== 'customer') {
            abort(403);
        }

        $user->acceptTerms($validated['document']);

        return response()->json([
            'ok'      => true,
            'message' => 'Terima kasih, persetujuan kamu sudah dicatat.',
        ]);
    }
}
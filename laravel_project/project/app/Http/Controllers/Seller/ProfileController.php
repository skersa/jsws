<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $user    = auth()->user();
        $profile = $user->sellerProfile ?? new SellerProfile(['user_id' => $user->id]);

        $iban = $profile->iban;
        $ibanValid = is_string($iban) && preg_match('/^TR\d{24}$/', $iban);

        return Inertia::render('Seller/Profile/Edit', [
            'profileData' => [
                'name'                => $user->name,
                'email'               => $user->email,
                'phone'               => $user->phone ?? '',
                'verification_status' => $profile->verification_status ?? 'pending',
                'rejection_reason'    => $profile->rejection_reason,
                'company_name'        => $profile->company_name,
                'tax_number'          => $profile->tax_number,
                'verified_at'         => $profile->verified_at?->format('d.m.Y'),
                'iban_input'          => $ibanValid ? substr($iban, 2) : '',
                'iban_masked'         => $ibanValid ? (substr($iban, 0, 4) . ' **** **** **** ' . substr($iban, -4)) : null,
                'has_document'        => (bool) $profile->id_document_path,
                'document_updated'    => $profile->updated_at?->format('d.m.Y H:i'),
                'auctions_count'      => $user->auctions()->count(),
                'active_count'        => $user->auctions()->where('status', 'active')->count(),
            ],
        ]);
    }

    public function update(Request $request, string $section)
    {
        $user    = auth()->user();
        $profile = SellerProfile::firstOrCreate(['user_id' => $user->id]);

        match ($section) {
            'kisisel' => $this->updateKisisel($request, $user),
            'sirket'  => $this->updateSirket($request, $profile),
            'odeme'   => $this->updateOdeme($request, $profile),
            default   => abort(404),
        };

        return back()->with('success', 'Kaydedildi.')->with('profile_section', $section);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $profile = SellerProfile::firstOrCreate(['user_id' => auth()->id()]);

        if ($profile->id_document_path) {
            Storage::disk('private')->delete($profile->id_document_path);
        }

        $path = $request->file('id_document')->store('seller/documents/' . auth()->id(), 'private');
        $profile->update([
            'id_document_path'    => $path,
            'verification_status' => 'pending',
            'rejection_reason'    => null,
        ]);

        return back()->with('success', 'Belge yüklendi, inceleme bekleniyor.')->with('profile_section', 'belge');
    }

    /* ── Private helpers ── */

    private function updateKisisel(Request $request, $user): void
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($data);
    }

    private function updateSirket(Request $request, SellerProfile $profile): void
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:150',
            'tax_number'   => 'nullable|string|max:20|unique:seller_profiles,tax_number,' . $profile->id,
        ]);
        $profile->update($data);
    }

    private function updateOdeme(Request $request, SellerProfile $profile): void
    {
        $data = $request->validate([
            'iban' => ['nullable', 'string', 'regex:/^TR\d{24}$/'],
        ], [
            'iban.regex' => 'IBAN formatı geçersiz. TR ile başlayan 26 haneli olmalı.',
        ]);
        $profile->update($data);
    }
}

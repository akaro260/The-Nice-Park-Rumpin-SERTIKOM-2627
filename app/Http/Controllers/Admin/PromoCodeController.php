<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        $promoCodes = PromoCode::query()
            ->latest()
            ->paginate(15);

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'code' => is_string($request->input('code'))
                ? Str::upper(trim($request->input('code')))
                : $request->input('code'),
        ]);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9][A-Z0-9-]*$/',
                Rule::unique('promo_codes', 'code'),
            ],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => [
                'required',
                'integer',
                'min:1',
                $request->input('discount_type') === 'percentage'
                    ? 'max:99'
                    : 'max:9999999999',
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        PromoCode::query()->create($data);

        return to_route('admin.promo-codes.index')
            ->with('success', 'Kode redeem berhasil dibuat.');
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($promoCode, $isActive): void {
            $lockedPromoCode = PromoCode::query()
                ->lockForUpdate()
                ->findOrFail($promoCode->id);

            if ($isActive && $lockedPromoCode->redeemed_at) {
                throw ValidationException::withMessages([
                    'is_active' => 'Kode yang sudah berhasil digunakan tidak dapat diaktifkan kembali.',
                ]);
            }

            $lockedPromoCode->update(['is_active' => $isActive]);
        });

        return to_route('admin.promo-codes.index')
            ->with('success', $isActive ? 'Kode redeem diaktifkan.' : 'Kode redeem dinonaktifkan.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($promoCode): bool {
            $lockedPromoCode = PromoCode::query()
                ->lockForUpdate()
                ->findOrFail($promoCode->id);

            if ($lockedPromoCode->reserved_payment_id || $lockedPromoCode->redeemed_at) {
                return false;
            }

            $lockedPromoCode->delete();

            return true;
        });

        if (! $deleted) {
            return to_route('admin.promo-codes.index')
                ->with('error', 'Kode yang sedang dipakai atau sudah berhasil digunakan tidak dapat dihapus.');
        }

        return to_route('admin.promo-codes.index')
            ->with('success', 'Kode redeem berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Ticket::query()
            ->withCount('registrations')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create(Request $request): View
    {
        return view('admin.products.form', [
            'product' => new Ticket([
                'type' => $request->query('type', 'general'),
                'is_active' => true,
                'quota' => 1,
                'discount_is_active' => false,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $product = Ticket::query()->create([
            ...$data,
            'available_quota' => $data['quota'],
        ]);

        return to_route('admin.products.edit', $product)
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Ticket $product): View
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Ticket $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($product, $data): void {
            $lockedProduct = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if (
                $lockedProduct->type !== $data['type'] &&
                $lockedProduct->registrations()->exists()
            ) {
                throw ValidationException::withMessages([
                    'type' => 'Jenis produk tidak dapat diubah setelah memiliki pesanan.',
                ]);
            }

            $dateQuotas = DB::table('ticket_date_quotas')
                ->where('ticket_id', $lockedProduct->id)
                ->lockForUpdate()
                ->get();

            foreach ($dateQuotas as $dateQuota) {
                $usedQuota = $dateQuota->quota - $dateQuota->available_quota;

                if ($data['quota'] < $usedQuota) {
                    throw ValidationException::withMessages([
                        'quota' => 'Kuota baru tidak boleh kurang dari jumlah peserta yang sudah dipesan.',
                    ]);
                }

                DB::table('ticket_date_quotas')
                    ->where('id', $dateQuota->id)
                    ->update([
                        'quota' => $data['quota'],
                        'available_quota' => $data['quota'] - $usedQuota,
                        'updated_at' => now(),
                    ]);
            }

            $lockedProduct->update([
                ...$data,
                'available_quota' => $data['quota'],
            ]);
        });

        return to_route('admin.products.index')
            ->with('success', 'Perubahan produk berhasil disimpan.');
    }

    public function destroy(Ticket $product): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($product): bool {
            $lockedProduct = Ticket::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if (
                $lockedProduct->registrations()->exists() ||
                DB::table('ticket_date_quotas')->where('ticket_id', $lockedProduct->id)->exists()
            ) {
                return false;
            }

            $lockedProduct->delete();

            return true;
        });

        if (! $deleted) {
            return to_route('admin.products.index')
                ->with('error', 'Produk yang memiliki riwayat kunjungan tidak dapat dihapus. Nonaktifkan produk agar riwayat tetap tersimpan.');
        }

        return to_route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array{
     *     name: string,
     *     type: string,
     *     description: ?string,
     *     price: int,
     *     quota: int,
     *     is_active: bool,
     *     discount_type: ?string,
     *     discount_value: ?int,
     *     discount_starts_on: ?string,
     *     discount_ends_on: ?string,
     *     discount_is_active: bool
     * }
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:general,school'],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'integer', 'min:1', 'max:9999999999'],
            'quota' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'is_active' => ['required', 'boolean'],
            'discount_is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['discount_is_active'] = $request->boolean('discount_is_active');

        $discountFields = [
            'discount_type',
            'discount_value',
            'discount_starts_on',
            'discount_ends_on',
        ];
        $hasDiscountConfiguration = collect($discountFields)
            ->contains(fn (string $field): bool => $request->filled($field));

        if ($data['discount_is_active'] || $hasDiscountConfiguration) {
            $discountValueRules = [
                'required',
                'integer',
                'min:1',
                $request->input('discount_type') === 'percentage'
                    ? 'max:99'
                    : 'max:9999999999',
            ];
            $discountData = $request->validate([
                'discount_type' => ['required', 'in:percentage,fixed'],
                'discount_value' => $discountValueRules,
                'discount_starts_on' => ['required', 'date_format:Y-m-d'],
                'discount_ends_on' => [
                    'required',
                    'date_format:Y-m-d',
                    'after_or_equal:discount_starts_on',
                ],
            ]);

            $discountValue = (int) $discountData['discount_value'];

            if ($discountData['discount_type'] === 'fixed' && $discountValue >= $data['price']) {
                throw ValidationException::withMessages([
                    'discount_value' => 'Nominal diskon harus lebih kecil dari harga normal.',
                ]);
            }

            if ($discountData['discount_type'] === 'percentage') {
                $discountedPrice = intdiv(
                    $data['price'] * (100 - $discountValue) + 50,
                    100,
                );

                if ($discountedPrice < 1) {
                    throw ValidationException::withMessages([
                        'discount_value' => 'Diskon harus menyisakan harga minimal Rp1 setelah pembulatan.',
                    ]);
                }
            }

            $data = [...$data, ...$discountData];
            $data['discount_value'] = $discountValue;
        } else {
            $data = [
                ...$data,
                'discount_type' => null,
                'discount_value' => null,
                'discount_starts_on' => null,
                'discount_ends_on' => null,
            ];
        }

        return $data;
    }
}

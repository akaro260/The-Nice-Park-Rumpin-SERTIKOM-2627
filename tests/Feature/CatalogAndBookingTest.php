<?php

use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Registration;
use App\Models\Ticket;
use App\Models\User;
use App\Services\MidtransGateway;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

function createCatalogProductForTest(array $overrides = []): Ticket
{
    return Ticket::query()->create(array_merge([
        'name' => 'Tiket Kunjungan',
        'type' => 'general',
        'description' => 'Informasi tiket.',
        'price' => 75000,
        'quota' => 100,
        'available_quota' => 100,
        'is_active' => true,
    ], $overrides));
}

function createAdminForCatalogTest(): User
{
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    return $admin;
}

function createCustomerForCatalogTest(): User
{
    $customer = User::factory()->create();
    $customer->forceFill(['role' => 'customer'])->save();

    return $customer;
}

function createPromoCodeForTest(array $overrides = []): PromoCode
{
    return PromoCode::query()->create(array_merge([
        'code' => 'RUMPIN10',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'is_active' => true,
    ], $overrides));
}

it('allows admins to manage products and keeps customers out of the admin catalog', function () {
    $customer = createCustomerForCatalogTest();
    $this->actingAs($customer)
        ->get(route('admin.products.index'))
        ->assertForbidden();

    $admin = createAdminForCatalogTest();
    $today = now()->toDateString();
    $tomorrow = now()->addDay()->toDateString();
    $this->actingAs($admin)
        ->post(route('admin.products.store'), [
            'name' => 'Paket Sekolah',
            'type' => 'school',
            'description' => 'Harga tetap untuk satu pengajuan.',
            'price' => 250000,
            'quota' => 60,
            'is_active' => 1,
        ])
        ->assertRedirect();

    $product = Ticket::query()->where('name', 'Paket Sekolah')->firstOrFail();
    $this->assertDatabaseHas('tickets', [
        'id' => $product->id,
        'type' => 'school',
        'price' => 250000,
        'quota' => 60,
        'available_quota' => 60,
        'is_active' => true,
    ]);

    $this->patch(route('admin.products.update', $product), [
        'name' => 'Paket Sekolah Baru',
        'type' => 'school',
        'description' => 'Harga terkini.',
        'price' => 300000,
        'quota' => 80,
        'is_active' => 0,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'discount_starts_on' => $today,
        'discount_ends_on' => $tomorrow,
        'discount_is_active' => 1,
    ])->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseHas('tickets', [
        'id' => $product->id,
        'name' => 'Paket Sekolah Baru',
        'price' => 300000,
        'quota' => 80,
        'is_active' => false,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'discount_is_active' => true,
    ]);

    $this->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSeeText('Diskon');
    $this->get(route('admin.products.index'))
        ->assertOk()
        ->assertSeeText('10%');

    $this->post(route('admin.products.store'), [
        'name' => 'Tiket Diskon Tetap',
        'type' => 'general',
        'price' => 100000,
        'quota' => 20,
        'is_active' => 1,
        'discount_type' => 'fixed',
        'discount_value' => 15000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $tomorrow,
        'discount_is_active' => 1,
    ])->assertRedirect();

    $this->assertDatabaseHas('tickets', [
        'name' => 'Tiket Diskon Tetap',
        'discount_type' => 'fixed',
        'discount_value' => 15000,
        'discount_is_active' => true,
    ]);
});

it('allows admins to create one-time redeem codes and blocks customers from code management', function () {
    $customer = createCustomerForCatalogTest();
    $this->actingAs($customer)
        ->get(route('admin.promo-codes.index'))
        ->assertForbidden();

    $admin = createAdminForCatalogTest();

    $this->actingAs($admin)
        ->post(route('admin.promo-codes.store'), [
            'code' => '  rumpin-hemat  ',
            'discount_type' => 'fixed',
            'discount_value' => 15000,
            'is_active' => 1,
        ])
        ->assertRedirect(route('admin.promo-codes.index'))
        ->assertSessionHas('success');

    $promoCode = PromoCode::query()->where('code', 'RUMPIN-HEMAT')->firstOrFail();

    expect($promoCode->discount_type)->toBe('fixed')
        ->and($promoCode->discount_value)->toBe('15000.00')
        ->and($promoCode->is_active)->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.promo-codes.index'))
        ->assertOk()
        ->assertSeeText('RUMPIN-HEMAT')
        ->assertSeeText('Buat kode baru');

    $this->assertDatabaseHas('promo_codes', [
        'code' => 'RUMPIN-HEMAT',
        'discount_type' => 'fixed',
        'discount_value' => 15000,
    ]);

    $this->post(route('admin.promo-codes.store'), [
        'code' => 'RUMPIN-HEMAT',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'is_active' => 1,
    ])->assertSessionHasErrors('code');

    $this->post(route('admin.promo-codes.store'), [
        'code' => 'BAD CODE',
        'discount_type' => 'percentage',
        'discount_value' => 100,
        'is_active' => 1,
    ])->assertSessionHasErrors(['code', 'discount_value']);

    $this->patch(route('admin.promo-codes.update', $promoCode), [
        'is_active' => 0,
    ])->assertRedirect(route('admin.promo-codes.index'));

    expect($promoCode->fresh()->is_active)->toBeFalse();
});

it('prevents deletion of products with booking history', function () {
    $admin = createAdminForCatalogTest();
    $unusedProduct = createCatalogProductForTest(['name' => 'Produk Belum Terjual']);
    $product = createCatalogProductForTest();
    $customer = createCustomerForCatalogTest();
    $registration = Registration::query()->create([
        'user_id' => $customer->id,
        'ticket_id' => $product->id,
        'registration_code' => 'TNR-HISTORY-001',
        'quantity' => 1,
        'total_price' => 75000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('tickets', ['id' => $product->id]);
    $this->assertDatabaseHas('registrations', ['id' => $registration->id]);

    $this->delete(route('admin.products.destroy', $unusedProduct))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('tickets', ['id' => $unusedProduct->id]);
});

it('shows only active products and refuses inactive product details', function () {
    $activeTicket = createCatalogProductForTest();
    $inactiveTicket = createCatalogProductForTest([
        'name' => 'Tiket Nonaktif',
        'is_active' => false,
    ]);

    $this->get(route('catalog.show', $activeTicket))
        ->assertOk()
        ->assertSeeText('Tiket Kunjungan')
        ->assertSeeText('Rp 75.000');

    $this->get(route('catalog.show', $inactiveTicket))
        ->assertNotFound();

    $this->actingAs(createCustomerForCatalogTest())
        ->post(route('bookings.store', $inactiveTicket), [
            'visit_date' => '2027-01-15',
            'quantity' => 1,
        ])->assertNotFound();
});

it('calculates general ticket totals from the database and reserves quota by date', function () {
    $customer = createCustomerForCatalogTest();
    $ticket = createCatalogProductForTest(['quota' => 20, 'available_quota' => 20]);

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 150000 &&
                $parameters['item_details'][0]['price'] === 75000 &&
                $parameters['item_details'][0]['quantity'] === 2
            ))
            ->andReturn((object) [
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/token',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('bookings.store', $ticket), [
            'visit_date' => '2027-01-15',
            'quantity' => 2,
            'price' => 1,
            'total_price' => 2,
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/token');

    $registration = Registration::query()->firstOrFail();
    $payment = Payment::query()->firstOrFail();

    expect($registration->total_price)->toBe('150000.00')
        ->and($registration->status)->toBe('awaiting_payment')
        ->and($payment->gross_amount)->toBe('150000.00')
        ->and($payment->snap_token)->toBe('snap-token');

    $this->assertDatabaseHas('ticket_date_quotas', [
        'ticket_id' => $ticket->id,
        'visit_date' => '2027-01-15',
        'quota' => 20,
        'available_quota' => 18,
    ]);
});

it('keeps school applications pending and charges the fixed package price only after approval', function () {
    $customer = createCustomerForCatalogTest();
    $admin = createAdminForCatalogTest();
    $package = createCatalogProductForTest([
        'name' => 'Paket Sekolah',
        'type' => 'school',
        'price' => 250000,
        'quota' => 60,
        'available_quota' => 60,
    ]);

    $this->actingAs($customer)
        ->post(route('bookings.store', $package), [
            'school_name' => 'SMP Rumpin',
            'contact_person' => 'Ayu',
            'visit_date' => '2027-02-10',
            'quantity' => 25,
            'total_price' => 1,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success');

    $registration = Registration::query()->firstOrFail();

    expect($registration->status)->toBe('pending_approval')
        ->and($registration->total_price)->toBe('250000.00')
        ->and($registration->quantity)->toBe(25);
    $this->assertDatabaseCount('ticket_date_quotas', 0);

    $this->actingAs($customer)
        ->from(route('dashboard'))
        ->post(route('payments.checkout', $registration))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors('registration');

    $this->actingAs($admin)
        ->patch(route('admin.applications.approve', $registration))
        ->assertRedirect(route('admin.applications.index'));

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Kode redeem (opsional)');

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 250000 &&
                $parameters['item_details'][0]['price'] === 250000 &&
                $parameters['item_details'][0]['quantity'] === 1
            ))
            ->andReturn((object) [
                'token' => 'school-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/school-token',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('payments.checkout', $registration))
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/school-token');

    $registration->refresh();
    $payment = Payment::query()->firstOrFail();

    expect($registration->status)->toBe('awaiting_payment')
        ->and($payment->gross_amount)->toBe('250000.00');

    $this->assertDatabaseHas('ticket_date_quotas', [
        'ticket_id' => $package->id,
        'visit_date' => '2027-02-10',
        'available_quota' => 35,
    ]);
});

it('verifies payment status with Midtrans before issuing one e-ticket', function () {
    $customer = createCustomerForCatalogTest();
    $ticket = createCatalogProductForTest(['quota' => 20, 'available_quota' => 20]);
    $registration = Registration::query()->create([
        'user_id' => $customer->id,
        'ticket_id' => $ticket->id,
        'registration_code' => 'TNR-PAYMENT-001',
        'visit_date' => '2027-01-15',
        'quantity' => 2,
        'total_price' => 150000,
        'status' => 'awaiting_payment',
    ]);
    $payment = Payment::query()->create([
        'registration_id' => $registration->id,
        'user_id' => $customer->id,
        'order_id' => 'TNR-1-ABCDEFGH',
        'gross_amount' => 150000,
        'transaction_status' => 'pending',
    ]);
    DB::table('ticket_date_quotas')->insert([
        'ticket_id' => $ticket->id,
        'visit_date' => '2027-01-15',
        'quota' => 20,
        'available_quota' => 18,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    config(['services.midtrans.signature_key' => 'test-signature-secret']);

    $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($payment): void {
        $mock->shouldReceive('verifyNotificationSignature')->twice()->andReturnTrue();
        $mock->shouldReceive('transactionStatus')
            ->twice()
            ->with($payment->order_id)
            ->andReturn((object) [
                'order_id' => $payment->order_id,
                'gross_amount' => '150000.00',
                'status_code' => '200',
                'transaction_status' => 'settlement',
                'transaction_id' => 'midtrans-transaction-1',
                'payment_type' => 'bank_transfer',
            ]);
    });

    $payload = [
        'order_id' => $payment->order_id,
        'status_code' => '200',
        'gross_amount' => '150000.00',
        'signature_key' => str_repeat('a', 128),
    ];

    $this->postJson(route('midtrans.notifications'), $payload)->assertOk();
    $registration->refresh();
    $issuedCode = $registration->e_ticket_code;

    expect($registration->status)->toBe('confirmed')
        ->and($registration->issued_at)->not->toBeNull()
        ->and($issuedCode)->not->toBeNull();

    $this->postJson(route('midtrans.notifications'), $payload)->assertOk();

    expect($registration->fresh()->e_ticket_code)->toBe($issuedCode);
    expect($payment->fresh()->paid_at)->not->toBeNull();
    $this->assertDatabaseHas('ticket_date_quotas', [
        'ticket_id' => $ticket->id,
        'visit_date' => '2027-01-15',
        'available_quota' => 18,
    ]);
});

it('rejects invalid Midtrans notification signatures', function () {
    config(['services.midtrans.signature_key' => 'test-signature-secret']);

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('verifyNotificationSignature')->once()->andReturnFalse();
        $mock->shouldNotReceive('transactionStatus');
    });

    $this->postJson(route('midtrans.notifications'), [
        'order_id' => 'TNR-1-ABCDEFGH',
        'status_code' => '200',
        'gross_amount' => '150000.00',
        'signature_key' => str_repeat('a', 128),
    ])->assertForbidden();
});

it('checks the Midtrans signature using the configured signature secret key', function () {
    config(['services.midtrans.signature_key' => 'test-signature-secret']);

    $notification = [
        'order_id' => 'TNR-1-ABCDEFGH',
        'status_code' => '200',
        'gross_amount' => '150000.00',
    ];
    $notification['signature_key'] = hash(
        'sha512',
        $notification['order_id'].$notification['status_code'].$notification['gross_amount'].'test-signature-secret',
    );

    expect(app(MidtransGateway::class)->verifyNotificationSignature($notification))->toBeTrue();

    $notification['gross_amount'] = '1.00';

    expect(app(MidtransGateway::class)->verifyNotificationSignature($notification))->toBeFalse();
});

it('shows active discounts with the normal and rounded promotional price', function () {
    $today = now()->toDateString();
    $ticket = createCatalogProductForTest([
        'price' => 1001,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);
    $expiredTicket = createCatalogProductForTest([
        'name' => 'Tiket Promo Berakhir',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'discount_starts_on' => now()->subDay()->toDateString(),
        'discount_ends_on' => now()->subDay()->toDateString(),
        'discount_is_active' => true,
    ]);
    $futureTicket = createCatalogProductForTest([
        'name' => 'Tiket Promo Mendatang',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'discount_starts_on' => now()->addDay()->toDateString(),
        'discount_ends_on' => now()->addDays(2)->toDateString(),
        'discount_is_active' => true,
    ]);
    $disabledTicket = createCatalogProductForTest([
        'name' => 'Tiket Promo Nonaktif',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Harga normal: Rp 1.001')
        ->assertSeeText('Promo')
        ->assertSeeText('Rp 901');

    $this->get(route('catalog.show', $ticket))
        ->assertOk()
        ->assertSeeText('Harga normal: Rp 1.001')
        ->assertSeeText('Promo')
        ->assertSeeText('Rp 901');

    $this->get(route('catalog.show', $expiredTicket))
        ->assertOk()
        ->assertSeeText('Rp 75.000')
        ->assertDontSeeText('Harga normal:');
    $this->get(route('catalog.show', $futureTicket))
        ->assertSeeText('Rp 75.000')
        ->assertDontSeeText('Harga normal:');
    $this->get(route('catalog.show', $disabledTicket))
        ->assertSeeText('Rp 75.000')
        ->assertDontSeeText('Harga normal:');
});

it('uses rounded percentage discounts when calculating checkout totals', function () {
    $customer = createCustomerForCatalogTest();
    $today = now()->toDateString();
    $ticket = createCatalogProductForTest([
        'price' => 1001,
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 901 &&
                $parameters['item_details'][0]['price'] === 901
            ))
            ->andReturn((object) [
                'token' => 'rounded-discount-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/rounded-discount',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 1,
            'price' => 1,
            'total_price' => 2,
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/rounded-discount');

    $payment = Payment::query()->firstOrFail();

    expect($payment->subtotal_amount)->toBe('1001.00')
        ->and($payment->discount_amount)->toBe('100.00')
        ->and($payment->gross_amount)->toBe('901.00');
});

it('applies redeem codes instead of product discounts and consumes them only after payment succeeds', function () {
    $customer = createCustomerForCatalogTest();
    $anotherCustomer = createCustomerForCatalogTest();
    $today = now()->toDateString();
    $ticket = createCatalogProductForTest([
        'price' => 100000,
        'discount_type' => 'fixed',
        'discount_value' => 50000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);
    $promoCode = createPromoCodeForTest();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 90000 &&
                $parameters['item_details'][0]['price'] === 90000
            ))
            ->andReturn((object) [
                'token' => 'redeem-code-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/redeem-code',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 1,
            'redeem_code' => 'rumpin10',
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/redeem-code');

    $payment = Payment::query()->firstOrFail();

    expect($payment->subtotal_amount)->toBe('100000.00')
        ->and($payment->discount_type)->toBe('percentage')
        ->and($payment->discount_value)->toBe('10.00')
        ->and($payment->discount_amount)->toBe('10000.00')
        ->and($payment->gross_amount)->toBe('90000.00')
        ->and($payment->promo_code)->toBe('RUMPIN10')
        ->and($promoCode->fresh()->reserved_payment_id)->toBe($payment->id)
        ->and($promoCode->fresh()->redeemed_at)->toBeNull();

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('RUMPIN10');

    config(['services.midtrans.signature_key' => 'test-signature-secret']);
    $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($payment): void {
        $mock->shouldReceive('verifyNotificationSignature')->once()->andReturnTrue();
        $mock->shouldReceive('transactionStatus')
            ->once()
            ->with($payment->order_id)
            ->andReturn((object) [
                'order_id' => $payment->order_id,
                'gross_amount' => '90000.00',
                'status_code' => '200',
                'transaction_status' => 'settlement',
            ]);
    });

    $this->postJson(route('midtrans.notifications'), [
        'order_id' => $payment->order_id,
        'status_code' => '200',
        'gross_amount' => '90000.00',
        'signature_key' => str_repeat('a', 128),
    ])->assertOk();

    expect($promoCode->fresh()->reserved_payment_id)->toBeNull()
        ->and($promoCode->fresh()->redeemed_payment_id)->toBe($payment->id)
        ->and($promoCode->fresh()->redeemed_by_user_id)->toBe($customer->id)
        ->and($promoCode->fresh()->redeemed_at)->not->toBeNull();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('createSnapTransaction');
    });

    $this->actingAs($anotherCustomer)
        ->from(route('catalog.show', $ticket))
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 1,
            'redeem_code' => 'RUMPIN10',
        ])
        ->assertRedirect(route('catalog.show', $ticket))
        ->assertSessionHasErrors('redeem_code');

    $this->assertDatabaseCount('payments', 1);
});

it('allows a school customer to redeem a fixed code after approval once per application', function () {
    $customer = createCustomerForCatalogTest();
    $admin = createAdminForCatalogTest();
    $today = now()->toDateString();
    $package = createCatalogProductForTest([
        'name' => 'Paket Diskon Kode',
        'type' => 'school',
        'price' => 250000,
        'quota' => 60,
        'available_quota' => 60,
        'discount_type' => 'fixed',
        'discount_value' => 50000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);
    $promoCode = createPromoCodeForTest([
        'code' => 'SEKOLAH25',
        'discount_type' => 'fixed',
        'discount_value' => 25000,
    ]);

    $this->actingAs($customer)
        ->post(route('bookings.store', $package), [
            'school_name' => 'SMP Rumpin',
            'contact_person' => 'Ayu',
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 25,
        ])
        ->assertRedirect(route('dashboard'));

    $registration = Registration::query()->firstOrFail();
    $this->actingAs($admin)
        ->patch(route('admin.applications.approve', $registration))
        ->assertRedirect(route('admin.applications.index'));

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 225000 &&
                $parameters['item_details'][0]['price'] === 225000 &&
                $parameters['item_details'][0]['quantity'] === 1
            ))
            ->andReturn((object) [
                'token' => 'school-redeem-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/school-redeem',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('payments.checkout', $registration), [
            'redeem_code' => 'sekolah25',
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/school-redeem');

    $payment = Payment::query()->firstOrFail();

    expect($payment->subtotal_amount)->toBe('250000.00')
        ->and($payment->discount_type)->toBe('fixed')
        ->and($payment->discount_value)->toBe('25000.00')
        ->and($payment->discount_amount)->toBe('25000.00')
        ->and($payment->gross_amount)->toBe('225000.00')
        ->and($payment->promo_code)->toBe('SEKOLAH25')
        ->and($promoCode->fresh()->reserved_payment_id)->toBe($payment->id);

    $this->assertDatabaseHas('ticket_date_quotas', [
        'ticket_id' => $package->id,
        'visit_date' => $registration->visit_date->toDateString(),
        'available_quota' => 35,
    ]);
});

it('releases a redeem code after an expired payment so it can be used again', function () {
    $customer = createCustomerForCatalogTest();
    $anotherCustomer = createCustomerForCatalogTest();
    $ticket = createCatalogProductForTest();
    $promoCode = createPromoCodeForTest();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->andReturn((object) [
                'token' => 'expired-redeem-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/expired-redeem',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 1,
            'redeem_code' => $promoCode->code,
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/expired-redeem');

    $expiredPayment = Payment::query()->firstOrFail();

    $this->actingAs($anotherCustomer)
        ->from(route('catalog.show', $ticket))
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 1,
            'redeem_code' => $promoCode->code,
        ])
        ->assertRedirect(route('catalog.show', $ticket))
        ->assertSessionHasErrors('redeem_code');

    config(['services.midtrans.signature_key' => 'test-signature-secret']);
    $this->mock(MidtransGateway::class, function (MockInterface $mock) use ($expiredPayment): void {
        $mock->shouldReceive('verifyNotificationSignature')->once()->andReturnTrue();
        $mock->shouldReceive('transactionStatus')
            ->once()
            ->with($expiredPayment->order_id)
            ->andReturn((object) [
                'order_id' => $expiredPayment->order_id,
                'gross_amount' => '67500.00',
                'status_code' => '200',
                'transaction_status' => 'expire',
            ]);
    });

    $this->postJson(route('midtrans.notifications'), [
        'order_id' => $expiredPayment->order_id,
        'status_code' => '200',
        'gross_amount' => '67500.00',
        'signature_key' => str_repeat('a', 128),
    ])->assertOk();

    expect($promoCode->fresh()->reserved_payment_id)->toBeNull()
        ->and($promoCode->fresh()->redeemed_at)->toBeNull();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->andReturn((object) [
                'token' => 'retry-redeem-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/retry-redeem',
            ]);
    });

    $this->post(route('bookings.store', $ticket), [
        'visit_date' => now()->addDays(2)->toDateString(),
        'quantity' => 1,
        'redeem_code' => $promoCode->code,
    ])->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/retry-redeem');

    $secondPayment = Payment::query()->latest('id')->firstOrFail();

    expect($secondPayment->promo_code)->toBe($promoCode->code)
        ->and($promoCode->fresh()->reserved_payment_id)->toBe($secondPayment->id)
        ->and($promoCode->fresh()->redeemed_at)->toBeNull();
});

it('rejects a fixed redeem code when its value is not below the selected product price', function () {
    $customer = createCustomerForCatalogTest();
    $ticket = createCatalogProductForTest();
    $promoCode = createPromoCodeForTest([
        'code' => 'TOO-MUCH',
        'discount_type' => 'fixed',
        'discount_value' => 75000,
    ]);

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('createSnapTransaction');
    });

    $this->actingAs($customer)
        ->from(route('catalog.show', $ticket))
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 1,
            'redeem_code' => $promoCode->code,
        ])
        ->assertRedirect(route('catalog.show', $ticket))
        ->assertSessionHasErrors('redeem_code');

    $this->assertDatabaseCount('registrations', 0);
    $this->assertDatabaseCount('payments', 0);
    expect($promoCode->fresh()->reserved_payment_id)->toBeNull();

    $tinyTicket = createCatalogProductForTest([
        'name' => 'Tiket Harga Minimum',
        'price' => 1,
    ]);
    $roundingCode = createPromoCodeForTest([
        'code' => 'ROUND-TO-NORMAL',
        'discount_value' => 1,
    ]);

    $this->post(route('bookings.store', $tinyTicket), [
        'visit_date' => now()->addDay()->toDateString(),
        'quantity' => 1,
        'redeem_code' => $roundingCode->code,
    ])->assertSessionHasErrors('redeem_code');

    $this->assertDatabaseCount('registrations', 0);
    $this->assertDatabaseCount('payments', 0);
    expect($roundingCode->fresh()->reserved_payment_id)->toBeNull();
});

it('rejects a redeem code with an invalid stored discount type', function () {
    $customer = createCustomerForCatalogTest();
    $ticket = createCatalogProductForTest();
    $promoCode = createPromoCodeForTest();
    $promoCode->forceFill(['discount_type' => 'unknown'])->save();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('createSnapTransaction');
    });

    $this->actingAs($customer)
        ->from(route('catalog.show', $ticket))
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 1,
            'redeem_code' => $promoCode->code,
        ])
        ->assertRedirect(route('catalog.show', $ticket))
        ->assertSessionHasErrors('redeem_code');

    $this->assertDatabaseCount('registrations', 0);
    $this->assertDatabaseCount('payments', 0);
    expect($promoCode->fresh()->reserved_payment_id)->toBeNull();
});

it('rejects discounts that can reduce the product price below one rupiah', function () {
    $admin = createAdminForCatalogTest();
    $today = now()->toDateString();

    $this->actingAs($admin)
        ->from(route('admin.products.create'))
        ->post(route('admin.products.store'), [
            'name' => 'Tiket Tanpa Harga',
            'type' => 'general',
            'price' => 1,
            'quota' => 10,
            'is_active' => 1,
            'discount_type' => 'percentage',
            'discount_value' => 99,
            'discount_starts_on' => $today,
            'discount_ends_on' => $today,
            'discount_is_active' => 1,
        ])
        ->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors('discount_value');

    foreach ([0, 101] as $percentage) {
        $this->post(route('admin.products.store'), [
            'name' => "Diskon {$percentage} Persen",
            'type' => 'general',
            'price' => 1000,
            'quota' => 10,
            'is_active' => 1,
            'discount_type' => 'percentage',
            'discount_value' => $percentage,
            'discount_starts_on' => $today,
            'discount_ends_on' => $today,
            'discount_is_active' => 1,
        ])->assertSessionHasErrors('discount_value');
    }

    $this->post(route('admin.products.store'), [
        'name' => 'Tiket Gratis',
        'type' => 'general',
        'price' => 1000,
        'quota' => 10,
        'is_active' => 1,
        'discount_type' => 'fixed',
        'discount_value' => 1000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => 1,
    ])->assertSessionHasErrors('discount_value');

    $this->post(route('admin.products.store'), [
        'name' => 'Tiket Potongan Melebihi Harga',
        'type' => 'general',
        'price' => 1000,
        'quota' => 10,
        'is_active' => 1,
        'discount_type' => 'fixed',
        'discount_value' => 1001,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => 1,
    ])->assertSessionHasErrors('discount_value');

    $this->post(route('admin.products.store'), [
        'name' => 'Tiket Diskon Penuh',
        'type' => 'general',
        'price' => 1000,
        'quota' => 10,
        'is_active' => 1,
        'discount_type' => 'percentage',
        'discount_value' => 100,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => 1,
    ])->assertSessionHasErrors('discount_value');

    $this->post(route('admin.products.store'), [
        'name' => 'Tiket Rentang Salah',
        'type' => 'general',
        'price' => 1000,
        'quota' => 10,
        'is_active' => 1,
        'discount_type' => 'fixed',
        'discount_value' => 100,
        'discount_starts_on' => now()->addDay()->toDateString(),
        'discount_ends_on' => $today,
        'discount_is_active' => 1,
    ])->assertSessionHasErrors('discount_ends_on');
});

it('uses backend discounted prices and stores an immutable payment snapshot for general tickets', function () {
    $customer = createCustomerForCatalogTest();
    $today = now()->toDateString();
    $ticket = createCatalogProductForTest([
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 130000 &&
                $parameters['item_details'][0]['price'] === 65000 &&
                $parameters['item_details'][0]['quantity'] === 2
            ))
            ->andReturn((object) [
                'token' => 'discounted-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/discounted-token',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('bookings.store', $ticket), [
            'visit_date' => now()->addDay()->toDateString(),
            'quantity' => 2,
            'price' => 1,
            'total_price' => 2,
        ])
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/discounted-token');

    $registration = Registration::query()->firstOrFail();
    $payment = Payment::query()->firstOrFail();

    expect($registration->total_price)->toBe('130000.00')
        ->and($payment->subtotal_amount)->toBe('150000.00')
        ->and($payment->discount_type)->toBe('fixed')
        ->and($payment->discount_value)->toBe('10000.00')
        ->and($payment->discount_amount)->toBe('20000.00')
        ->and($payment->gross_amount)->toBe('130000.00');

    $ticket->update([
        'price' => 90000,
        'discount_value' => 20000,
    ]);

    expect($payment->fresh()->subtotal_amount)->toBe('150000.00')
        ->and($payment->fresh()->discount_value)->toBe('10000.00')
        ->and($payment->fresh()->gross_amount)->toBe('130000.00');

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Rp 150.000')
        ->assertSeeText('Rp 130.000')
        ->assertSeeText('hemat Rp 20.000');
});

it('prices school discounts once per application and recalculates only after a payment expires', function () {
    $customer = createCustomerForCatalogTest();
    $admin = createAdminForCatalogTest();
    $today = now()->toDateString();
    $package = createCatalogProductForTest([
        'name' => 'Paket Promo Sekolah',
        'type' => 'school',
        'price' => 250000,
        'quota' => 60,
        'available_quota' => 60,
        'discount_type' => 'fixed',
        'discount_value' => 50000,
        'discount_starts_on' => $today,
        'discount_ends_on' => $today,
        'discount_is_active' => true,
    ]);

    $this->actingAs($customer)
        ->post(route('bookings.store', $package), [
            'school_name' => 'SMP Rumpin',
            'contact_person' => 'Ayu',
            'visit_date' => now()->addDays(2)->toDateString(),
            'quantity' => 25,
        ])
        ->assertRedirect(route('dashboard'));

    $registration = Registration::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('admin.applications.approve', $registration))
        ->assertRedirect(route('admin.applications.index'));

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 200000 &&
                $parameters['item_details'][0]['price'] === 200000 &&
                $parameters['item_details'][0]['quantity'] === 1
            ))
            ->andReturn((object) [
                'token' => 'school-discount-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/school-discount',
            ]);
        $mock->shouldReceive('verifyNotificationSignature')->once()->andReturnTrue();
        $mock->shouldReceive('transactionStatus')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnUsing(fn (string $orderId): object => (object) [
                'order_id' => $orderId,
                'gross_amount' => '200000.00',
                'status_code' => '200',
                'transaction_status' => 'expire',
            ]);
    });

    $this->actingAs($customer)
        ->post(route('payments.checkout', $registration))
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/school-discount');

    $firstPayment = Payment::query()->firstOrFail();

    expect($firstPayment->subtotal_amount)->toBe('250000.00')
        ->and($firstPayment->discount_amount)->toBe('50000.00')
        ->and($firstPayment->gross_amount)->toBe('200000.00');

    $package->update(['price' => 300000]);
    $this->travelTo(now()->addDay());

    $this->post(route('payments.checkout', $registration))
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/school-discount');
    $this->assertDatabaseCount('payments', 1);

    config(['services.midtrans.signature_key' => 'test-signature-secret']);
    $this->postJson(route('midtrans.notifications'), [
        'order_id' => $firstPayment->order_id,
        'status_code' => '200',
        'gross_amount' => '200000.00',
        'signature_key' => str_repeat('a', 128),
    ])->assertOk();

    $this->mock(MidtransGateway::class, function (MockInterface $mock): void {
        $mock->shouldReceive('createSnapTransaction')
            ->once()
            ->with(Mockery::on(fn (array $parameters): bool => $parameters['transaction_details']['gross_amount'] === 300000 &&
                $parameters['item_details'][0]['price'] === 300000 &&
                $parameters['item_details'][0]['quantity'] === 1
            ))
            ->andReturn((object) [
                'token' => 'school-full-price-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/transactions/school-full-price',
            ]);
    });

    $this->post(route('payments.checkout', $registration))
        ->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/transactions/school-full-price');

    $payments = Payment::query()->orderBy('id')->get();

    expect($payments)->toHaveCount(2)
        ->and($payments[0]->gross_amount)->toBe('200000.00')
        ->and($payments[1]->subtotal_amount)->toBe('300000.00')
        ->and($payments[1]->discount_type)->toBeNull()
        ->and($payments[1]->discount_amount)->toBe('0.00')
        ->and($payments[1]->gross_amount)->toBe('300000.00');
});

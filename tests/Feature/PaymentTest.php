<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Booking;
use App\Models\Court;
use App\Models\TimeSlot;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\Booking
     */
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();
        // make necessary data
        // create court and timeslot directly (factories not defined)
        $court = Court::create([ 'name' => 'Test Court', 'price_per_hour' => 100000, 'slug' => 'test-court', 'status' => 'active' ]);
        $slot = TimeSlot::create([
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'display_text' => '08:00-09:00',
            'status' => 'active',
        ]);
        $this->booking = Booking::create([
            'booking_code' => 'BKG-TEST',
            'court_id' => $court->id,
            'time_slot_id' => $slot->id,
            'date' => now()->addDay(),
            'customer_name' => 'Tester',
            'phone' => '08123456789',
            'total_price' => 100000,
            'paid' => 0,
            'remaining' => 100000,
            'status' => 'pending',
        ]);
    }

    public function test_proof_file_validation_rejects_disallowed_mime()
    {
        Storage::fake('public');

        $response = $this->post(route('payment.process', $this->booking), [
            'payment_type' => 'full',
            'payment_method' => 'bca',
            'proof_file' => UploadedFile::fake()->create('document.exe', 100),
        ]);

        $response->assertSessionHasErrors('proof_file');
    }

    public function test_qris_payment_completes_without_proof()
    {
        $response = $this->post(route('payment.process', $this->booking), [
            'payment_type' => 'full',
            'payment_method' => 'qris',
        ]);

        $response->assertRedirect(route('booking.detail', $this->booking));
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'qris',
            'status' => 'completed',
        ]);
    }
}

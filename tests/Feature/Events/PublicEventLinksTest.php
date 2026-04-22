<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEventLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_bookable_event_reserve_url_redirects_to_public_info_page(): void
    {
        $therapist = User::factory()->create([
            'slug' => 'therapeute-test',
            'company_name' => 'Cabinet Test',
        ]);

        $event = Event::create([
            'user_id' => $therapist->id,
            'name' => 'Atelier découverte',
            'description' => 'Description',
            'start_date_time' => now()->addWeek(),
            'duration' => 90,
            'booking_required' => false,
            'limited_spot' => false,
            'number_of_spot' => null,
            'associated_product' => null,
            'image' => null,
            'showOnPortail' => true,
            'location' => 'Cabinet',
            'event_type' => 'in_person',
            'visio_provider' => null,
            'visio_url' => null,
            'visio_token' => null,
            'collect_payment' => false,
            'price' => null,
            'tax_rate' => 0,
        ]);

        $this->get(route('events.reserve.create', $event))
            ->assertRedirect(route('events.public.show', $event));
    }

    public function test_public_info_page_is_accessible_for_non_bookable_events(): void
    {
        $therapist = User::factory()->create([
            'slug' => 'therapeute-test',
            'company_name' => 'Cabinet Test',
        ]);

        $event = Event::create([
            'user_id' => $therapist->id,
            'name' => 'Atelier découverte',
            'description' => 'Description',
            'start_date_time' => now()->addWeek(),
            'duration' => 90,
            'booking_required' => false,
            'limited_spot' => false,
            'number_of_spot' => null,
            'associated_product' => null,
            'image' => null,
            'showOnPortail' => true,
            'location' => 'Cabinet',
            'event_type' => 'in_person',
            'visio_provider' => null,
            'visio_url' => null,
            'visio_token' => null,
            'collect_payment' => false,
            'price' => null,
            'tax_rate' => 0,
        ]);

        $response = $this->get(route('events.public.show', $event));

        $response->assertOk();
        $response->assertSee('Atelier découverte');
        $response->assertSee('Sans réservation en ligne');
    }
}

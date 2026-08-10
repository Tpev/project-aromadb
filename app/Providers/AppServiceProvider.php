<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use App\Models\ClientProfile;
use App\Policies\ClientProfilePolicy;
use Carbon\Carbon;
use App\Services\IpInfoService;
use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Policies\OfferJourneyPolicy;
use App\Domain\OfferJourneys\Policies\OfferJourneyContactPolicy;
use App\Domain\OfferJourneys\Services\OfferJourneyConversionAttributor;
use App\Models\Appointment;
use App\Models\Reservation;
use App\Models\DigitalTrainingEnrollment;
use App\Models\GiftVoucherOrder;
use App\Services\AppointmentMailDeliveryGuard;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
             $this->app->bind(IpInfoService::class, function ($app) {
            return new IpInfoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		 Carbon::setLocale('fr');
		  Event::listen(MessageSending::class, AppointmentMailDeliveryGuard::class);
		  // Queue workers are long-lived: close any cached SMTP transport between jobs.
		  Queue::looping(static function (): void {
		      Mail::purge();
		  });
		  \Illuminate\Support\Facades\Gate::policy(ClientProfile::class, ClientProfilePolicy::class);
          \Illuminate\Support\Facades\Gate::policy(OfferJourney::class, OfferJourneyPolicy::class);
          \Illuminate\Support\Facades\Gate::policy(OfferJourneyContact::class, OfferJourneyContactPolicy::class);
          Appointment::saved(fn (Appointment $appointment) => config('offer_journeys.enabled')
              ? app(OfferJourneyConversionAttributor::class)->appointment($appointment) : null);
          Reservation::saved(fn (Reservation $reservation) => config('offer_journeys.enabled')
              ? app(OfferJourneyConversionAttributor::class)->reservation($reservation) : null);
          DigitalTrainingEnrollment::created(fn (DigitalTrainingEnrollment $enrollment) => config('offer_journeys.enabled')
              ? app(OfferJourneyConversionAttributor::class)->training($enrollment) : null);
          GiftVoucherOrder::saved(fn (GiftVoucherOrder $order) => config('offer_journeys.enabled')
              ? app(OfferJourneyConversionAttributor::class)->giftVoucher($order) : null);
        // Listen for login event and update login count and last login time
        Event::listen(Login::class, function ($event) {
            $user = $event->user;

            // Update login count and last login timestamp
            $user->login_count = $user->login_count + 1;
            $user->last_login_at = Carbon::now();
            $user->save();
        });
		
		
    }
}

<?php

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    public function testCustomerCanPurchaseConcertTicket()
    {
        $paymentGateway = new FakePaymentGateway;

        $this->app->instance(PaymentGateway::class, $paymentGateway);

        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'  => 'andy@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'andy@example.com')->first();

        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets->count());
    }
}

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

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;

        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    public function testCustomerCanPurchaseConcertTicket()
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'  => 'andy@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'andy@example.com')->first();

        $this->assertNotNull($order);

        $this->assertEquals(3, $order->tickets()->count());
    }

    public function testEmailIsRequiredToPurchaseTickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $this->assertArrayHasKey('email', $this->decodeResponseJson());
    }

    public function testEmailIsValidToPurchaseTickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'not-valid-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $this->assertArrayHasKey('email', $this->decodeResponseJson());
    }

    public function testTicketQuantityIsRequiredToPurchaseTickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'endy@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $this->assertArrayHasKey('ticket_quantity', $this->decodeResponseJson());
    }

    public function testTicketQuantityMustBeAtLeastOneToPurchaseTickets()
    {
        $concert = factory(Concert::class)->create();

        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'endy@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $this->assertArrayHasKey('ticket_quantity', $this->decodeResponseJson());
    }
}

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

    private function orderTickets($params, $specified_concert = null)
    {
        if ($specified_concert){
            $concert = $specified_concert;
        } else {
            $concert = factory(Concert::class)->states('published')->create();
        }

        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    public function testCustomerCanPurchasePublishedConcertTickets()
    {
        $concert = factory(Concert::class)->states('published')->create([ 'ticket_price' => 3250 ]);
        $concert->addTickets(4);

        $this->orderTickets([
            'email'  => 'andy@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ], $concert);

        $this->assertResponseStatus(201);

        $this->seeJsonSubset([
            'email' => 'andy@example.com',
            'ticket_quantity' => 3,
            'amount' => 9750,
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('andy@example.com'));
        $this->assertEquals(3 , $concert->ordersFor('andy@example.com')->first()->ticketQuantity());
    }

    public function testCannotPurchaseTicketsToUnpublishedConcerts()
    {
        $concert = factory(Concert::class)->states('unpublished')->create([ 'ticket_price' => 3250 ]);
        $concert->addTickets(4);

        $this->orderTickets([
            'email'  => 'andy@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ], $concert);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    public function testAnOrderIsNotCreatedWhenFails()
    {
        $concert = factory(Concert::class)->states('published')->create([ 'ticket_price' => 3250 ]);
        $concert->addTickets(20);

        $this->orderTickets([
            'email'  => 'andy@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ], $concert);

        $this->assertResponseStatus(422);

        $order = $concert->orders()->where('email', 'andy@example.com')->first();

        $this->assertNull($order);
    }

    public function testEmailIsRequiredToPurchaseTickets()
    {
        $this->orderTickets([
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    public function testCannotPurchaseMoreTicketsThanRemain()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(50);

        $this->orderTickets([
            'email'  => 'andy@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ], $concert);

        $this->assertResponseStatus(422);

        $order = $concert->orders()->where('email', 'andy@example.com')->first();

        $this->assertNull($order);
        $this->assertEquals(0, $this->paymentGateway->totalCharges());

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function testEmailIsValidToPurchaseTickets()
    {
        $this->orderTickets([
            'email' => 'not-valid-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    public function testTicketQuantityIsRequiredToPurchaseTickets()
    {
        $this->orderTickets([
            'email' => 'endy@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    public function testTicketQuantityMustBeAtLeastOneToPurchaseTickets()
    {
        $this->orderTickets([
            'email' => 'endy@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    public function testPaymentTokenIsRequiredToPurchaseTickets()
    {
        $this->orderTickets([
            'email' => 'endy@example.com',
            'ticket_quantity' => 1,
        ]);

        $this->assertValidationError('payment_token');
    }

    public function testPurchaseTicketsThatAnotherCustomerTryingToPurchase()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 1200,
        ]);

        $concert->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function () use ($concert) {
            $this->orderTickets([
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ], $concert);
            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $this->orderTickets([
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ], $concert);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3 , $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }
}

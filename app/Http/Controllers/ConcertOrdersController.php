<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Models\Concert;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate($request, [
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1|max:10',
            'payment_token' => 'required',
        ]);

        try {
            $order = $concert->orderTickets(
                $request->input('email'),
                $request->input('ticket_quantity')
            );

            $this->paymentGateway->charge(
                $request->input('ticket_quantity') * $concert->ticket_price,
                $request->input('payment_token')
            );

            return response()->json([], 201);

        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {

            return response()->json([], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

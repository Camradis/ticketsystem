@extends('layouts.main')

@section('content')
</h1>
    {{ $concert->title }}
<h1>
<h2>
    {{ $concert->subtitle }}
</h2>
<p>
    {{ $concert->date->format('F j, Y') }}
</p>
<p>
    Doors at {{ $concert->date->format('g:ia') }}
</p>
<p>
    Price: {{ number_format($concert->ticket_price / 100, 2) }}
</p>
<p>
    Venue: {{ $concert->venue }}
</p>
<p>
    Venue address {{ $concert->venue_address }}
</p>
<p>
    City {{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}
</p>
<p>
    Additional information: {{ $concert->additional_information }}
</p>
@endsection
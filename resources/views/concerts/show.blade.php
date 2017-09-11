@extends('layouts.main')

@section('content')
</h1>
    {{ $concert->title }}
<h1>
<h2>
    {{ $concert->subtitle }}
</h2>
<p>
    {{ $concert->formatted_date }}
</p>
<p>
    Doors at {{ $concert->formatted_start_time }}
</p>
<p>
    Price: {{ $concert->ticket_price_in_dollars }}
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
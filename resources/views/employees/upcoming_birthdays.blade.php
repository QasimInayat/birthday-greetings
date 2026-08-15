@extends('layouts.scaffold')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fa-solid fa-cake-candles me-2"></i> Upcoming Birthdays</h4>

        <!-- Filters -->
        <div class="btn-group d-none">
            <button class="btn btn-outline-primary">This Week</button>
            <button class="btn btn-outline-primary">This Month</button>
            <button class="btn btn-outline-primary">This Year</button>
        </div>
    </div>

    <div class="row g-3">

        @forelse ($upcomingBirthdays as $birthday)
            <!-- Sample Card -->
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card shadow-sm border-0 text-center p-3 h-100">
                    <x-avatar :src="$birthday->profile_image" :name="$birthday->full_name" :size="80"
                              class="rounded-circle mx-auto d-block" />
                    <h5 class="mt-2 mb-1 text-break">{{ $birthday->full_name }}</h5>
                    <p class="text-muted mb-1 text-break">{{ $birthday->department ?: '—' }}</p>
                    <p class="small mb-0 mt-auto">🎂 {{ date('M d', strtotime($birthday->birthday)) }}</p>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No upcoming birthdays found.
                </div>
            </div>
        @endforelse

    </div>
</div>
@endsection

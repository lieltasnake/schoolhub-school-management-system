@extends('layouts.app')

@section('title', 'Fee Structure Details')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>{{ $feeStructure->feeCategory->name }}</h1>
            <p class="intro-copy">Fee assignment for {{ $feeStructure->grade->name }} &bull; {{ $feeStructure->academicYear->name }}</p>
        </div>
        <div class="intro-actions">
            @can('update', $feeStructure)
                <a href="{{ route('fee-structures.edit', $feeStructure) }}" class="primary-link">Edit structure</a>
            @endcan
            <a href="{{ route('fee-structures.index') }}" class="secondary-link">All structures</a>
        </div>
    </section>

    <div class="dashboard-columns">
        <section class="content-card">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="section-heading">
                <div>
                    <p class="eyebrow">Pricing summary</p>
                    <h2>Structure details</h2>
                </div>
                <span class="status-pill {{ $feeStructure->status }}">{{ ucfirst($feeStructure->status) }}</span>
            </div>

            <div class="profile-grid">
                <div>
                    <span class="meta-label">Fee category</span>
                    <strong>{{ $feeStructure->feeCategory->name }} ({{ $feeStructure->feeCategory->code }})</strong>
                </div>
                <div>
                    <span class="meta-label">Amount</span>
                    <strong style="font-size: 1.25rem; color: #0f766e;">${{ number_format((float) $feeStructure->amount, 2) }}</strong>
                </div>
                <div>
                    <span class="meta-label">Applicable grade</span>
                    <strong>{{ $feeStructure->grade->name }} (Level {{ $feeStructure->grade->level }})</strong>
                </div>
                <div>
                    <span class="meta-label">Academic year</span>
                    <strong>{{ $feeStructure->academicYear->name }}</strong>
                </div>
                <div>
                    <span class="meta-label">Term / Schedule</span>
                    <strong>{{ $feeStructure->term ? $feeStructure->term->name : 'Full Academic Year' }}</strong>
                </div>
                <div>
                    <span class="meta-label">Campus</span>
                    <strong>{{ $feeStructure->branch->name }}</strong>
                </div>
                <div>
                    <span class="meta-label">Mandatory fee?</span>
                    <strong>{{ $feeStructure->feeCategory->is_mandatory ? 'Yes (All students in this grade)' : 'No (Optional / Supplemental)' }}</strong>
                </div>
                <div>
                    <span class="meta-label">Configured date</span>
                    <strong>{{ $feeStructure->created_at->format('M d, Y') }}</strong>
                </div>
            </div>

            @if ($feeStructure->description)
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <span class="meta-label">Description / Notes</span>
                    <p>{{ $feeStructure->description }}</p>
                </div>
            @endif

            @can('delete', $feeStructure)
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                    <form method="POST" action="{{ route('fee-structures.destroy', $feeStructure) }}" onsubmit="return confirm('Are you sure you want to delete this fee structure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="secondary-button" style="color: #dc2626; border-color: #fca5a5;">Delete fee structure</button>
                    </form>
                </div>
            @endcan
        </section>

        <section class="content-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Category information</p>
                    <h2>Category overview</h2>
                </div>
            </div>

            <div class="profile-grid">
                <div>
                    <span class="meta-label">Category name</span>
                    <strong>{{ $feeStructure->feeCategory->name }}</strong>
                </div>
                <div>
                    <span class="meta-label">Category code</span>
                    <code>{{ $feeStructure->feeCategory->code }}</code>
                </div>
                <div>
                    <span class="meta-label">Status</span>
                    <span class="status-pill {{ $feeStructure->feeCategory->status }}">{{ ucfirst($feeStructure->feeCategory->status) }}</span>
                </div>
            </div>

            @if ($feeStructure->feeCategory->description)
                <div style="margin-top: 1.25rem;">
                    <span class="meta-label">Category details</span>
                    <p>{{ $feeStructure->feeCategory->description }}</p>
                </div>
            @endif
        </section>
    </div>
@endsection

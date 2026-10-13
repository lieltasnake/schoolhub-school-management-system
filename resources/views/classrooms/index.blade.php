@extends('layouts.app')

@section('title', 'Classroom Management')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Facilities</p>
            <h1>Classrooms</h1>
            <p class="intro-copy">Manage physical rooms, science labs, auditoriums, and seat capacities.</p>
        </div>
        <a href="{{ route('classrooms.create') }}" class="primary-link">Add classroom</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('classrooms.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, room #, building..." aria-label="Search classrooms">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="type" aria-label="Filter by type">
                <option value="">All types</option>
                @foreach (['standard', 'science_lab', 'computer_lab', 'auditorium', 'art_studio', 'gymnasium'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                @endforeach
            </select>
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'maintenance', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Room #</th>
                        <th>Name</th>
                        <th>Campus</th>
                        <th>Building / Floor</th>
                        <th>Capacity</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($classrooms as $room)
                        <tr>
                            <td><strong>{{ $room->room_number }}</strong></td>
                            <td>{{ $room->name }}</td>
                            <td>{{ $room->branch->name }}</td>
                            <td>{{ $room->building ?? 'Main' }} / {{ $room->floor ?? '1' }}</td>
                            <td>{{ $room->capacity }} seats</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $room->type)) }}</td>
                            <td><span class="status-pill {{ $room->status }}">{{ ucfirst($room->status) }}</span></td>
                            <td>
                                <a href="{{ route('classrooms.edit', $room) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('classrooms.destroy', $room) }}" onsubmit="return confirm('Delete this classroom?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No classrooms found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $classrooms->links() }}
    </section>
@endsection

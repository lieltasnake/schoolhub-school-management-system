@extends('layouts.app')

@section('title', $classroom ? 'Edit Classroom' : 'Create Classroom')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Facilities</p>
            <h1>{{ $classroom ? 'Edit classroom' : 'Create classroom' }}</h1>
            <p class="intro-copy">Set up classroom room numbers, location details, and student capacity.</p>
        </div>
        <a href="{{ route('classrooms.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $classroom ? route('classrooms.update', $classroom) : route('classrooms.store') }}" class="form-grid">
            @csrf
            @if ($classroom)
                @method('PUT')
            @endif

            <div class="full-width">
                <label for="branch_id">Campus</label>
                <select id="branch_id" name="branch_id" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $classroom?->branch_id) === (string) $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="room_number">Room number / Code</label>
                <input id="room_number" name="room_number" value="{{ old('room_number', $classroom?->room_number) }}" maxlength="50" placeholder="e.g. 101, LAB-A, AUD-1" required>
            </div>

            <div>
                <label for="name">Room name</label>
                <input id="name" name="name" value="{{ old('name', $classroom?->name) }}" maxlength="255" placeholder="e.g. Grade 1A Classroom, Physics Lab" required>
            </div>

            <div>
                <label for="building">Building (optional)</label>
                <input id="building" name="building" value="{{ old('building', $classroom?->building) }}" maxlength="255" placeholder="e.g. Science Block, Building A">
            </div>

            <div>
                <label for="floor">Floor (optional)</label>
                <input id="floor" name="floor" value="{{ old('floor', $classroom?->floor) }}" maxlength="50" placeholder="e.g. Ground Floor, 2nd Floor">
            </div>

            <div>
                <label for="capacity">Student capacity</label>
                <input id="capacity" name="capacity" type="number" min="1" max="1000" value="{{ old('capacity', $classroom?->capacity ?? 30)" required>
            </div>

            <div>
                <label for="type">Classroom type</label>
                <select id="type" name="type" required>
                    @foreach (['standard', 'science_lab', 'computer_lab', 'auditorium', 'art_studio', 'gymnasium'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $classroom?->type ?? 'standard') === $type)>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['active', 'maintenance', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $classroom?->status ?? 'active') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $classroom ? 'Update classroom' : 'Create classroom' }}</button>
            </div>
        </form>
    </section>
@endsection

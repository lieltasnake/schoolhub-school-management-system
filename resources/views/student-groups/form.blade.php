@extends('layouts.app')

@section('title', $group ? 'Edit Student Group' : 'New Student Group')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students / Groups</p>
            <h1>{{ $group ? "Edit group: {$group->name}" : 'New student group' }}</h1>
            <p class="intro-copy">Configure student clubs, houses, transportation rosters, or cohorts.</p>
        </div>
        <a href="{{ route('student-groups.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please resolve the following issues:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $group ? route('student-groups.update', $group) : route('student-groups.store') }}" class="form-grid">
            @csrf
            @if ($group)
                @method('PUT')
            @endif

            @if ($group)
                <div>
                    <label>Campus</label>
                    <input type="text" value="{{ $group->branch->name }}" disabled>
                </div>
            @else
                <div>
                    <label for="branch_id">Campus <span style="color: #ef4444;">*</span></label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Select campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="name">Group Name <span style="color: #ef4444;">*</span></label>
                <input id="name" name="name" value="{{ old('name', $group?->name) }}" placeholder="e.g. Blue Falcon House, Chess Club" required>
            </div>

            <div>
                <label for="code">Unique Code <span style="color: #ef4444;">*</span></label>
                <input id="code" name="code" value="{{ old('code', $group?->code) }}" placeholder="e.g. BF-HOUSE, CHESS-01" required>
            </div>

            <div>
                <label for="type">Group Category <span style="color: #ef4444;">*</span></label>
                <select id="type" name="type" required>
                    <option value="house" @selected(old('type', $group?->type) === 'house')>House / Team</option>
                    <option value="club" @selected(old('type', $group?->type ?? 'club') === 'club')>Club / Society</option>
                    <option value="sports" @selected(old('type', $group?->type) === 'sports')>Sports Team</option>
                    <option value="transport" @selected(old('type', $group?->type) === 'transport')>Transport Route</option>
                    <option value="academic" @selected(old('type', $group?->type) === 'academic')>Academic Cohort</option>
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $group?->is_active ?? true))>
                <label for="is_active" style="margin: 0; font-weight: 500;">Active</label>
            </div>

            <div class="full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="2" placeholder="Activities, objectives, schedule...">{{ old('description', $group?->description) }}</textarea>
            </div>

            <div class="full-width" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('student-groups.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $group ? 'Update group' : 'Create group' }}</button>
            </div>
        </form>
    </section>
@endsection

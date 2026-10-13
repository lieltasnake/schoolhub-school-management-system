@extends('layouts.app')

@section('title', "Group: {$group->name}")

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Students / Groups</p>
            <h1>{{ $group->name }}</h1>
            <p class="intro-copy">Code: <strong>{{ $group->code }}</strong> &bull; Category: <span style="text-transform: capitalize;">{{ $group->type }}</span> &bull; Campus: {{ $group->branch->name }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('student-groups.index') }}" class="secondary-link">All groups</a>
            <a href="{{ route('student-groups.edit', $group) }}" class="secondary-link">Edit details</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="dashboard-grid" style="margin-bottom: 1.5rem;">
        <article class="metric-card">
            <span class="metric-label">Total group members</span>
            <strong>{{ $group->students->count() }}</strong>
            <small>Active participating students</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Group category</span>
            <strong style="text-transform: capitalize;">{{ $group->type }}</strong>
            <small>{{ $group->branch->name }}</small>
        </article>
        <article class="metric-card">
            <span class="metric-label">Status</span>
            <strong>{{ $group->is_active ? 'Active' : 'Inactive' }}</strong>
            <small>Membership status</small>
        </article>
    </div>

    <section class="content-card" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Add Member to Group</h2>
        @if ($availableStudents->isEmpty())
            <p style="color: var(--muted); margin: 0;">All active students at this campus are already members of this group.</p>
        @else
            <form method="POST" action="{{ route('student-groups.members.add', $group) }}" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                @csrf
                <div style="flex: 1; min-width: 240px;">
                    <label for="student_id" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Select Student</label>
                    <select id="student_id" name="student_id" required style="width: 100%;">
                        <option value="">Choose student to add...</option>
                        @foreach ($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_number }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="width: 180px;">
                    <label for="role" style="font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; display: block;">Group Role</label>
                    <input id="role" name="role" placeholder="e.g. Member, Captain" value="member">
                </div>

                <button type="submit" class="primary-button">Add member</button>
            </form>
        @endif
    </section>

    <section class="content-card">
        <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Current Members ({{ $group->students->count() }})</h2>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission #</th>
                        <th>Student name</th>
                        <th>Class placement</th>
                        <th>Role in group</th>
                        <th>Joined date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($group->students as $member)
                        <tr>
                            <td>
                                <strong>{{ $member->admission_number }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('students.show', $member) }}" class="table-link" style="font-weight: 600;">
                                    {{ $member->full_name }}
                                </a>
                            </td>
                            <td>
                                {{ $member->latestEnrollment?->grade?->name ?? '—' }}
                                @if ($member->latestEnrollment?->section)
                                    ({{ $member->latestEnrollment->section->name }})
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background: var(--panel-soft); text-transform: capitalize;">
                                    {{ $member->pivot->role ?? 'Member' }}
                                </span>
                            </td>
                            <td>{{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('student-groups.members.remove', [$group, $member]) }}" onsubmit="return confirm('Remove this student from the group?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-link" style="color: var(--danger); background: none; border: none; padding: 0; cursor: pointer;">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--muted);">
                                No students have joined this group yet. Use the form above to add members.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@extends('layouts.app')

@section('title', $scale ? 'Edit Grade Scale' : 'New Grade Scale')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Exams & Assessments / Grade Scale</p>
            <h1>{{ $scale ? "Edit scale: {$scale->name}" : 'New grade scale' }}</h1>
            <p class="intro-copy">Define percentage thresholds, letter grades, points, and passing criteria.</p>
        </div>
        <a href="{{ route('grade-scales.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $scale ? route('grade-scales.update', $scale) : route('grade-scales.store') }}">
            @csrf
            @if ($scale)
                @method('PUT')
            @endif

            <div class="form-grid" style="margin-bottom: 2rem;">
                @if ($scale)
                    <div>
                        <label>Campus</label>
                        <input type="text" value="{{ $scale->branch->name }}" disabled>
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
                    <label for="name">Scale Name <span style="color: #ef4444;">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $scale?->name) }}" placeholder="e.g. Standard 4.0 Scale, Percentage Scale" required>
                </div>

                <div>
                    <label for="code">Unique Code <span style="color: #ef4444;">*</span></label>
                    <input id="code" name="code" value="{{ old('code', $scale?->code) }}" placeholder="e.g. SCALE-STD, SCALE-GPA" required>
                </div>

                <div>
                    <label for="status">Status <span style="color: #ef4444;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="active" @selected(old('status', $scale?->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $scale?->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                    <input type="checkbox" id="is_default" name="is_default" value="1" @checked(old('is_default', $scale?->is_default))>
                    <label for="is_default" style="margin: 0; font-weight: 500;">Set as default scale for this campus</label>
                </div>

                <div class="full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="2" placeholder="Scale explanation...">{{ old('description', $scale?->description) }}</textarea>
                </div>
            </div>

            <!-- Grade Tiers Table -->
            <div style="border-top: 1px solid var(--edge); padding-top: 1.5rem; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.1rem;">Grade Tiers & Percentage Bands <span style="color: #ef4444;">*</span></h3>
                        <small style="color: var(--muted);">Define letter marks, score ranges (0–100%), GPA points, and remarks.</small>
                    </div>
                    <button type="button" class="secondary-button" onclick="addGradeTierRow()">+ Add Tier</button>
                </div>

                <div class="table-wrap">
                    <table class="data-table" id="grade-tiers-table">
                        <thead>
                            <tr>
                                <th>Letter mark *</th>
                                <th>Min score (%) *</th>
                                <th>Max score (%) *</th>
                                <th>GPA Points</th>
                                <th>Remarks / Description</th>
                                <th>Pass?</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $defaultTiers = [
                                    ['letter_grade' => 'A+', 'min_percentage' => '90.00', 'max_percentage' => '100.00', 'grade_point' => '4.00', 'remarks' => 'Outstanding', 'is_pass' => true],
                                    ['letter_grade' => 'A',  'min_percentage' => '80.00', 'max_percentage' => '89.99',  'grade_point' => '3.75', 'remarks' => 'Excellent',   'is_pass' => true],
                                    ['letter_grade' => 'B',  'min_percentage' => '70.00', 'max_percentage' => '79.99',  'grade_point' => '3.00', 'remarks' => 'Very Good',   'is_pass' => true],
                                    ['letter_grade' => 'C',  'min_percentage' => '60.00', 'max_percentage' => '69.99',  'grade_point' => '2.00', 'remarks' => 'Good / Pass', 'is_pass' => true],
                                    ['letter_grade' => 'D',  'min_percentage' => '50.00', 'max_percentage' => '59.99',  'grade_point' => '1.00', 'remarks' => 'Pass',        'is_pass' => true],
                                    ['letter_grade' => 'F',  'min_percentage' => '0.00',  'max_percentage' => '49.99',  'grade_point' => '0.00', 'remarks' => 'Fail',        'is_pass' => false],
                                ];
                                $items = old('items', $scale ? $scale->items->toArray() : $defaultTiers);
                            @endphp

                            @foreach ($items as $idx => $tier)
                                <tr>
                                    <td>
                                        <input name="items[{{ $idx }}][letter_grade]" value="{{ $tier['letter_grade'] ?? '' }}" required style="width: 80px;" placeholder="A+">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" name="items[{{ $idx }}][min_percentage]" value="{{ $tier['min_percentage'] ?? '' }}" required style="width: 100px;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" name="items[{{ $idx }}][max_percentage]" value="{{ $tier['max_percentage'] ?? '' }}" required style="width: 100px;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="10" name="items[{{ $idx }}][grade_point]" value="{{ $tier['grade_point'] ?? '' }}" style="width: 90px;" placeholder="4.0">
                                    </td>
                                    <td>
                                        <input name="items[{{ $idx }}][remarks]" value="{{ $tier['remarks'] ?? '' }}" style="width: 150px;" placeholder="e.g. Excellent">
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="items[{{ $idx }}][is_pass]" value="1" @checked(!empty($tier['is_pass']))>
                                    </td>
                                    <td>
                                        <button type="button" class="text-link" style="color: var(--danger); background: none; border: none; cursor: pointer;" onclick="this.closest('tr').remove()">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <a href="{{ route('grade-scales.index') }}" class="secondary-link">Cancel</a>
                <button type="submit" class="primary-button">{{ $scale ? 'Update grade scale' : 'Create grade scale' }}</button>
            </div>
        </form>
    </section>

    <script>
        let tierIndex = {{ count($items) }};
        function addGradeTierRow() {
            const table = document.getElementById('grade-tiers-table').getElementsByTagName('tbody')[0];
            const row = table.insertRow();
            row.innerHTML = `
                <td><input name="items[${tierIndex}][letter_grade]" required style="width: 80px;" placeholder="Grade"></td>
                <td><input type="number" step="0.01" min="0" max="100" name="items[${tierIndex}][min_percentage]" required style="width: 100px;" value="0.00"></td>
                <td><input type="number" step="0.01" min="0" max="100" name="items[${tierIndex}][max_percentage]" required style="width: 100px;" value="100.00"></td>
                <td><input type="number" step="0.01" min="0" max="10" name="items[${tierIndex}][grade_point]" style="width: 90px;" placeholder="0.0"></td>
                <td><input name="items[${tierIndex}][remarks]" style="width: 150px;" placeholder="Remark"></td>
                <td style="text-align: center;"><input type="checkbox" name="items[${tierIndex}][is_pass]" value="1" checked></td>
                <td><button type="button" class="text-link" style="color: var(--danger); background: none; border: none; cursor: pointer;" onclick="this.closest('tr').remove()">Remove</button></td>
            `;
            tierIndex++;
        }
    </script>
@endsection

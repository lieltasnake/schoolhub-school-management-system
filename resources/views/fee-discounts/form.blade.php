@extends('layouts.app')

@section('title', $feeDiscount ? 'Edit Fee Discount' : 'Create Fee Discount')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>{{ $feeDiscount ? 'Edit discount' : 'Create discount' }}</h1>
            <p class="intro-copy">Configure waiver schemes and concessions applied to student billing invoices.</p>
        </div>
        <a href="{{ route('fee-discounts.index') }}" class="secondary-link">Cancel</a>
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

        <form method="POST" action="{{ $feeDiscount ? route('fee-discounts.update', $feeDiscount) : route('fee-discounts.store') }}" class="form-grid">
            @csrf
            @if ($feeDiscount)
                @method('PUT')
            @endif

            @unless ($feeDiscount)
                <div class="full-width">
                    <label for="branch_id">Campus</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', request('branch_id')) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label for="name">Discount name</label>
                <input id="name" name="name" value="{{ old('name', $feeDiscount?->name) }}" maxlength="100" placeholder="e.g. Sibling Discount or Merit Scholarship" required>
            </div>

            <div>
                <label for="code">Discount code</label>
                <input id="code" name="code" value="{{ old('code', $feeDiscount?->code) }}" maxlength="30" placeholder="e.g. SIB or SCHOL" required>
            </div>

            <div>
                <label for="type">Discount type</label>
                <select id="type" name="type" required>
                    <option value="percentage" @selected(old('type', $feeDiscount?->type) === 'percentage')>Percentage (%)</option>
                    <option value="fixed" @selected(old('type', $feeDiscount?->type) === 'fixed')>Fixed Amount ($)</option>
                </select>
            </div>

            <div>
                <label for="value">Discount value</label>
                <input id="value" type="number" step="0.01" min="0" name="value" value="{{ old('value', $feeDiscount?->value) }}" placeholder="e.g. 10.00 for 10% or 150.00" required>
                <small class="meta-line">Enter the percentage rate (0–100) or exact dollar amount deducted.</small>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $feeDiscount?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $feeDiscount?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="full-width">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Eligibility terms or conditions for this discount">{{ old('description', $feeDiscount?->description) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $feeDiscount ? 'Save changes' : 'Create discount' }}</button>
            </div>
        </form>
    </section>
@endsection

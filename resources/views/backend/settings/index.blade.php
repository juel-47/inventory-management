@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>General Settings</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-cog mr-2"></i> Configure Site & Currency</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Site Name</label>
                                            <input type="text" name="site_name" class="form-control" value="{{ $setting->site_name ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Email</label>
                                            <input type="email" name="contact_email" class="form-control" value="{{ $setting->contact_email ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="3">{{ $setting->address ?? '' }}</textarea>
                                </div>

                                <hr>
                                <h5 class="mb-3 text-primary">Currency Settings</h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Local Currency Name (e.g., BDT)</label>
                                            <input type="text" name="currency_name" class="form-control" value="{{ $setting->currency_name ?? 'USD' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Currency Icon (e.g., ৳)</label>
                                            <input type="text" name="currency_icon" class="form-control" value="{{ $setting->currency_icon ?? '$' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Exchange Rate (1 USD = ?)</label>
                                            <input type="number" step="0.0001" name="currency_rate" class="form-control" value="{{ $setting->currency_rate ?? 1.0000 }}" required>
                                            <small class="text-muted">Enter how much of your local currency equals 1 USD.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h4><i class="fas fa-info-circle mr-2"></i> Help Info</h4>
                        </div>
                        <div class="card-body">
                            <h6>How it works:</h6>
                            <p class="text-muted">The system uses **USD** as the base currency for all database entries. The **Exchange Rate** you set here is used to calculate and display the **Local Price** across all modules (Bookings, Purchases, Sales, and Reports).</p>
                            <div class="alert alert-light border">
                                <strong>Formula:</strong><br>
                                Local Price = Original (USD) * Rate
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

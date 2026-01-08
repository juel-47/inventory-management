@extends('backend.layouts.master')

@section('title')
Vendor
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Update Vendor</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Update Vendor</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendor.update', $vendor->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label>Shop Name</label>
                                <input type="text" class="form-control" name="shop_name" value="{{ $vendor->shop_name }}">
                            </div>

                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="{{ $vendor->phone }}">
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="{{ $vendor->email }}">
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" value="{{ $vendor->address }}">
                            </div>

                            <div class="form-group">
                                <label>Country</label>
                                <select name="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach (config('settings.country_list') as $country)
                                <option {{ $country === $vendor->country ? 'selected' : '' }} value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description">{{ $vendor->description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option {{ $vendor->status == 1 ? 'selected' : '' }} value="1">Active</option>
                                    <option {{ $vendor->status == 0 ? 'selected' : '' }} value="0">Inactive</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

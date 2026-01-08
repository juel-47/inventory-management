@extends('backend.layouts.master')

@section('title')
Vendor
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Vendor</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Vendor</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.vendor.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendor.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                <label>Shop Name</label>
                                <input type="text" class="form-control" name="shop_name" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" value="">
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Country</label>
                                <select name="country" class="form-control select2">
                                <option value="">Select</option>
                                @foreach (config('settings.country_list') as $country)
                                <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <label>Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>

                            
                            </div>

                            <button type="submit" class="btn btn-primary">Create</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

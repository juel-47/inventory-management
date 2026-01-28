<div class="row">
    @foreach ($products as $sku => $product)
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
        <div class="card h-100 border shadow-sm rounded-lg overflow-hidden product-card hover-shadow transition-all" style="border-color: #e3e6f0; background-color: #fdfdfd;">
            <div class="position-relative bg-white d-flex align-items-center justify-content-center" style="height: 220px;">
                <img alt="{{ $product->name }}" 
                        src="{{ $product->thumb_image ? asset('storage/'.$product->thumb_image) : asset('uploads/default.jpg') }}" 
                        class="img-fluid" 
                        style="max-height: 100%; max-width: 100%; object-fit: contain;">
                
                <!-- Checkbox / Status Badge -->
                <div class="position-absolute d-flex flex-column align-items-end" style="top: 10px; right: 10px; z-index: 10;">
                    @if(Auth::user()->can('Manage Products'))
                        <label class="custom-switch m-0">
                            <input type="checkbox" name="custom-switch-checkbox" data-id="{{ $product->id }}" class="custom-switch-input change-status" {{ $product->status ? 'checked' : '' }}>
                            <span class="custom-switch-indicator shadow-sm"></span>
                        </label>
                        <span class="status-message badge badge-success shadow-sm mt-1" style="display: none; font-size: 10px; opacity: 0.9;">Saved</span>
                    @else
                        <span class="badge {{ $product->status ? 'badge-success' : 'badge-danger' }} shadow-sm px-2 py-1">{{ $product->status ? 'Active' : 'Inactive' }}</span>
                    @endif
                </div>
            </div>

            <div class="card-body p-3 d-flex flex-column">
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <span class="badge badge-pill badge-light text-muted" style="font-size: 10px; padding: 5px 10px;">{{ $product->category->name ?? 'Uncategorized' }}</span>
                    <!-- Stock Badge -->
                    @php
                        $stock = $product->inventory_stock;
                        $badgeClass = $stock > 0 ? 'badge-info' : 'badge-danger';
                    @endphp
                    <span class="badge {{ $badgeClass }} badge-pill px-2 py-1" style="font-size: 10px;">Stock: {{ (float)$stock }}</span>
                </div>
                
                <h6 class="card-title text-dark font-weight-bold mb-2 text-truncate" title="{{ $product->name }}" style="font-size: 1rem;">
                    {{ $product->name }}
                </h6>

                <!-- Variants Info -->
                @if($product->variants->count() > 0)
                <div class="mb-3">
                    <div class="d-flex flex-wrap" style="gap: 4px;">
                        @foreach($product->variants->take(3) as $variant)
                            <span class="badge badge-light border text-muted shadow-none py-1 px-2" style="font-size: 9px; font-weight: 500;">
                                {{ $variant->name }}
                            </span>
                        @endforeach
                        @if($product->variants->count() > 3)
                            <span class="badge badge-light border text-muted shadow-none py-1 px-2" style="font-size: 9px;">+{{ $product->variants->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>
                @else
                <div class="mb-3" style="min-height: 23px;">
                     <!-- Spacer for alignment if no variants -->
                </div>
                @endif

                <div class="mt-auto bg-white border rounded p-3 shadow-sm">
                    @if(Auth::user()->can('Manage Products'))
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted" style="font-size: 13px; font-weight: 500;">Purchase:</span>
                            <span class="font-weight-bold text-dark" style="font-size: 15px;">{{ formatConverted($product->purchase_price) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted" style="font-size: 13px; font-weight: 500;">Outlet Price:</span>
                            <span class="font-weight-bold text-primary" style="font-size: 15px;">{{ formatConverted($product->outlet_price) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size: 13px; font-weight: 500;">Selling:</span>
                            <span class="font-weight-bold text-success" style="font-size: 16px;">{{ formatConverted($product->price) }}</span>
                        </div>
                    @elseif(Auth::user()->hasRole(['Outlet User', 'User']))
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted" style="font-size: 13px; font-weight: 500;">Buying Price:</span>
                            <span class="font-weight-bold text-primary" style="font-size: 15px;">{{ formatConverted($product->outlet_price) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted" style="font-size: 13px; font-weight: 500;">Selling Price:</span>
                            <span class="font-weight-bold text-success" style="font-size: 16px;">{{ formatConverted($product->price) }}</span>
                        </div>
                    @endif
                    
                    @can('Manage Order Place')
                    <!-- Add to Basket Button -->
                    <button type="button" class="btn btn-outline-info btn-sm btn-block mt-3 add-to-basket" data-id="{{ $product->id }}">
                        <i class="fas fa-shopping-basket mr-1"></i> Add to Basket
                    </button>
                    @endcan

                    @can('Create Product Requests')
                    <!-- Add to Request Basket Button -->
                    <button type="button" class="btn btn-outline-primary btn-sm btn-block mt-2 add-to-request-basket" data-id="{{ $product->id }}">
                        <i class="fas fa-file-import mr-1"></i> Add to Request Basket
                    </button>
                    @endcan
                </div>
                
                @can('Manage Products')
                <div class="mt-3 row no-gutters">
                    <div class="col-6 pr-1">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-primary btn-sm btn-block rounded-pill"><i class="fas fa-edit mr-1"></i> Edit</a>
                    </div>
                    <div class="col-6 pl-1">
                        <a href="{{ route('admin.products.destroy', $product->id) }}" class="btn btn-outline-danger btn-sm btn-block rounded-pill delete-item"><i class="fas fa-trash mr-1"></i> Delete</a>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row mt-4 mb-5">
    <div class="col-12 text-center">
        <p class="text-muted mb-3" style="font-size: 14px; font-weight: 500;">
            Showing <span class="text-dark font-weight-bold">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</span> 
            of <span class="text-dark font-weight-bold">{{ $products->total() }}</span> products
        </p>
        <div class="d-flex justify-content-center custom-pagination">
            {{ $products->links() }}
        </div>
    </div>
</div>

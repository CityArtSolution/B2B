@php
    $canEdit = \Illuminate\Support\Facades\Blade::check('hasPermission', 'shop.product.edit');
    $canDelete = \Illuminate\Support\Facades\Blade::check('hasPermission', 'shop.product.destroy');
@endphp

<!-- bulk actions, shown when products are selected -->
@if ($canEdit)
    <form method="POST" action="{{ route('shop.product.bulkUpdate') }}" id="bulkForm" class="bulk-bar d-none">
        @csrf
        <span class="fw-semibold"><span id="bulkCount">0</span> {{ __('selected') }}</span>
        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" name="action" value="activate" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i> {{ __('Turn on') }}</button>
            <button type="submit" name="action" value="deactivate" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye-slash"></i> {{ __('Turn off') }}</button>
            @if ($canDelete)
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" data-confirm-delete><i class="bi bi-trash"></i> {{ __('Delete') }}</button>
            @endif
        </div>
    </form>
@endif

<div class="table-responsive">
    <table class="table table-modern cards-on-mobile mb-0">
        <thead>
            <tr>
                @if ($canEdit)
                    <th style="width: 36px"><input type="checkbox" class="form-check-input" id="selectAll" aria-label="{{ __('Select all') }}"></th>
                @endif
                <th style="min-width: 240px">{{ __('Product') }}</th>
                <th>{{ __('Price') }}</th>
                <th>{{ __('Carton') }}</th>
                <th>{{ __('Stock') }}</th>
                <th class="text-center">{{ __('Status') }}</th>
                <th class="text-end">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php
                    $stock = (int) $product->stock_total;
                    $stockClass = $stock <= 0 ? 'out' : ($stock <= $lowStock ? 'low' : 'ok');
                    $branchStock = $product->productBranches->map(fn ($row) => ($row->branch?->name[app()->getLocale()] ?? $row->branch?->name['en'] ?? '-') . ': ' . (int) $row->qty)->implode(' • ');
                @endphp
                <tr>
                    @if ($canEdit)
                        <td><input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $product->id }}" form="bulkForm" aria-label="{{ __('Select') }}"></td>
                    @endif
                    <td data-label="{{ __('Product') }}">
                        <div class="d-flex align-items-center gap-2 text-start">
                            <img src="{{ $product->thumbnail }}" width="46" height="46" class="rounded object-fit-cover flex-shrink-0" alt="" loading="lazy">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate" style="max-width: 260px">
                                    {{ Str::limit($product->translated_name->name ?? $product->translated_name, 50, '...') }}
                                </div>
                                <div class="text-muted small">
                                    {{ __('Code') }}: <span dir="ltr">{{ $product->code }}</span>
                                    @if ($product?->brand)
                                        · {{ $product->brand->name }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td data-label="{{ __('Price') }}">
                        @if ($canEdit)
                            <button type="button" class="price-edit" data-url="{{ route('shop.product.quickUpdate', $product->id) }}"
                                data-price="{{ $product->price + 0 }}" data-discount="{{ $product->discount_price + 0 }}"
                                data-bs-toggle="tooltip" data-bs-title="{{ __('Click to change the price') }}">
                        @else
                            <span>
                        @endif
                            <span class="price-main fw-bold">{{ showCurrency($product->price) }}</span>
                            <small class="text-muted">/ {{ __('piece') }}</small>
                            @if ($canEdit)
                                <i class="bi bi-pencil small text-muted"></i>
                            @endif
                        @if ($canEdit)
                            </button>
                        @else
                            </span>
                        @endif
                    </td>
                    <td data-label="{{ __('Carton') }}">
                        @if ($product->carton_units_count)
                            <div>
                                <span class="carton-price fw-semibold">{{ showCurrency($product->discount_price > 0 ? $product->discount_price : $product->sale_carton_price) }}</span>
                                <del class="carton-old text-muted small {{ $product->discount_price > 0 ? '' : 'd-none' }}">{{ showCurrency($product->sale_carton_price) }}</del>
                            </div>
                            <div class="text-muted small">{{ __('Units Per Carton') }}: {{ $product->carton_units_count }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td data-label="{{ __('Stock') }}">
                        <span class="stock-badge {{ $stockClass }}" @if ($branchStock) data-bs-toggle="tooltip" data-bs-title="{{ $branchStock }}" @endif>
                            <i class="bi {{ $stockClass == 'ok' ? 'bi-check-circle' : ($stockClass == 'low' ? 'bi-exclamation-triangle' : 'bi-x-circle') }}"></i>
                            {{ $stock }} {{ __('cartons') }}
                        </span>
                    </td>
                    <td data-label="{{ __('Status') }}" class="text-center">
                        <label class="switch mb-0" data-bs-toggle="tooltip" data-bs-placement="left"
                            data-bs-title="{{ $product->is_active ? __('Visible in the store') : __('Hidden from the store') }}">
                            <a href="{{ route('shop.product.toggle', $product->id) }}">
                                <input type="checkbox" {{ $product->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </a>
                        </label>
                    </td>
                    <td class="text-end text-nowrap">
                        @hasPermission('shop.product.show')
                            <a href="{{ route('shop.product.show', $product->id) }}" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="tooltip" data-bs-title="{{ __('View Product') }}"><i class="bi bi-eye"></i></a>
                        @endhasPermission
                        @hasPermission('shop.product.edit')
                            <a href="{{ route('shop.product.edit', $product->id) }}" class="btn btn-sm btn-outline-info"
                                data-bs-toggle="tooltip" data-bs-title="{{ __('Edit Product') }}"><i class="bi bi-pencil-square"></i></a>
                        @endhasPermission
                        @hasPermission('shop.product.barcode')
                            <a href="{{ route('shop.product.barcode', $product->id) }}" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="tooltip" data-bs-title="{{ __('Generate Barcode for this product') }}"><i class="bi bi-upc-scan"></i></a>
                        @endhasPermission
                        @hasPermission('shop.product.destroy')
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteProduct({{ $product->id }})"
                                data-bs-toggle="tooltip" data-bs-title="{{ __('Delete Product') }}"><i class="bi bi-trash"></i></button>
                        @endhasPermission
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%">
                        <div class="empty-state">
                            <i class="bi bi-box-seam"></i>
                            <h6>{{ __('No products found') }}</h6>
                            <p>{{ __('Try changing or clearing the filters.') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

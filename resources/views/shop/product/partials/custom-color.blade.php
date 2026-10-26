<!-- add a custom color; it is saved to the color list when the product is saved -->
<div class="d-flex align-items-center gap-2 mt-2">
    <input type="color" id="customColorCode" class="form-control form-control-color flex-shrink-0" value="#000000"
        title="{{ __('Pick a color') }}">
    <input type="text" id="customColorName" class="form-control" maxlength="255"
        placeholder="{{ __('Custom color name') }}">
    <button type="button" class="btn btn-outline-primary text-nowrap" onclick="addCustomColor()">
        {{ __('Add Color') }}
    </button>
</div>

@push('scripts')
    <script>
        const escapeHtml = (value) => $('<div>').text(value ?? '').html().replace(/"/g, '&quot;');

        let customColorCount = 0;

        function addCustomColor() {
            const name = $('#customColorName').val().trim();
            if (!name) {
                toastr.error("{{ __('Please enter the color name') }}");
                return;
            }

            const option = $('<option>')
                .val('new-' + (++customColorCount))
                .text(name)
                .attr('data-name', name)
                .attr('data-color', $('#customColorCode').val())
                .prop('selected', true);

            $('.colorSelect').append(option).trigger('change');
            $('#customColorName').val('');
        }

        $(document).on('keydown', '#customColorName', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCustomColor();
            }
        });
    </script>
@endpush

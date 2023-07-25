
<div>
    <div class="row mb-3">
        <label for="{{ $name }}" class="col-md-4 col-form-label text-md-end">{{ __('base.'.$name) }}</label>
        @php $default_data = null @endphp
        <div class="col-md-6">
            @if ( $name === 'phone_number' )
                @php $default_data = Auth::user()->phone_number; @endphp
            @endif
            @if ( $name === 'email' )
                @php $default_data = Auth::user()->email; @endphp
            @endif
            @if ( $type === 'textarea' )
                <textarea id="{{ $name }}" class="form-control @error( $name ) is-invalid @enderror" name="{{ $name }}" required autocomplete="{{ $name }}" rows="3">{{ old( $name ) }}</textarea>
            @elseif ( $type === 'select' )
                <select id="{{ $name }}" class="form-control @error( $name ) is-invalid @enderror" name="{{ $name }}" required autocomplete="{{ $name }}">
                    <option value="" disabled selected>Wybierz trasę</option>
                    @foreach ( $options as $direction )
                        <option value="{{ $direction[ 'name' ] }}">{{ $direction[ 'print_name' ] }}</option>
                    @endforeach
                </select>

            @else
                <input id="{{ $name }}" type="{{ $type }}" class="form-control @error( $name ) is-invalid @enderror" name="{{ $name }}" value="{{ old( $name, $default_data ) }}" required autocomplete="{{ $name }}">
            @endif
            @error( $name )
                <span class="invalid-feedback" role="alert">
                    <strong> {{ $message }}</strong>
                </span>
            @enderror
        </div>
    </div>
</div>

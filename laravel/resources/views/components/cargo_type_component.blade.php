

<tr class="cargo_component_{{ $id }} align-middle h-100">
    <th scope="row" class="h3">{{ $id }}</th>
    <td>
        <input id="cargo_name_{{ $id }}" type="text" class="form-control @error( "cargo_name_" . $id ) is-invalid @enderror" name="cargo_name_{{ $id }}" autocomplete="cargo_name_{{ $id }}" value="{{ old( "cargo_name_" . $id  ) }}">
        <small id="cargo_name_info_{{ $id }}" class="form-text text-muted">{{ __( 'base.cargo_name_info' ) }}</small>
    </td>
    <td><textarea id="cargo_description_{{ $id }}" class="form-control @error( "cargo_description_" . $id ) is-invalid @enderror" name="cargo_description_{{ $id }}" autocomplete="cargo_description_{{ $id }}" rows="2">{{ old( "cargo_description_" . $id  ) }}</textarea></td>
    <td >
        <div class="price_container d-flex d-flex flex-column flex-sm-row">
            <input id="cargo_price_{{ $id }}" type="number" class=" form-group form-control @error( "cargo_price_" . $id ) is-invalid @enderror" name="cargo_price_{{ $id }}" value="{{ old( "cargo_price_" . $id  ) ? old( "cargo_price_" . $id  ) : "0" }}" autocomplete="cargo_price_{{ $id }}" min="0">
            <input type="hidden" name="select_currency_{{ $id }}" value="{{ old("select_currency_" . $id) !== null ? old("select_currency_" . $id) : "option_default" }}">
            <select id="select_currency_{{ $id }}" name="select_currency_{{ $id }}" required class=" form-group form-control @error( "select_currency_" . $id ) is-invalid @enderror">
                {{ $iterator = 1 }}
                <option value="{{ old("select_currency_" . $id) != "option_default" ? old("select_currency_" . $id) : "option_default" }}" disabled selected>{{ old("select_currency_" . $id) != "option_default" && old("select_currency_" . $id) != null ? old("select_currency_" . $id) : __('base.default_currency_option') }}</option>
                @foreach (json_decode( $currencies ) as $currency_option )
                    <option value="{{ $currency_option }}">{{ $currency_option }}</option>
                @endforeach
            </select>
        </div>
        <small id="cargo_price_info_{{ $id }}" class="form-text text-muted">{{ __( 'base.cargo_price_info' ) }}</small>
    </td>
    <td>
        <div class="action_cargo_container_button_{{ $id }}">
            <a href="#" data-toggle="tooltip" title="{{ __( 'base.courier_announcement_cargo_type_action_delete_info' ) }}">
                <button class="cargo_type_delete_btn_{{ $id }} btn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="white" class="bi bi-trash3" viewBox="0 0 16 16">
                        <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/>
                    </svg>
                </button>
            </a>
        </div>
        @if ( $id == 1 )
            <div class="action_cargo_container_info">
                <p>{{ __( 'base.courier_announcement_cargo_type_min_one_type' ) }}</p>
            </div>
        @endif
    </td>
</tr>

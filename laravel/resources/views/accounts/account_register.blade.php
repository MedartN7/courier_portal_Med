@extends('layouts.app')

@section('add_header')
    <script src="{{ asset('js/accounts_type_scripts.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/accounts_type_styles.css') }}">
@endsection

@section('content')

<div class="container">
    <form action="{{ route('confirmed_account') }}" method="POST" id="account_register_form">
        @csrf
        {{ $your_choice = '' }}
        <div class="account_options">
        <input type="hidden" name="account_type_input_id" value="{{ $your_choice }}">
        @foreach ( $accountData[ 'accounts_types' ] as $account )
            <div class="rectangle"  id="{{ $account[ 'id' ] }}">
                <div class="account_name">{{ __( 'base.account_confirmation_' . $account[ 'id' ] ) }}</div>
                    @foreach ( $account[ 'description' ] as $description )
                        <div class="acount_description">{{ __( 'base.account_confirmation_' . $description[ 'status' ] . '_title' ) }}</div>
                        <ul class="list_with_{{ $description[ 'status' ] }}">

                        @for ( $i = 0; $i < $description[ 'options' ]; $i++ )
                            <li>{{ __( 'base.account_confirmation_' . $account[ 'id' ] . '_' . $description[ 'status' ] . '_option_' . $i + 1 ) }}</li>
                        @endfor
                        </ul>
                    @endforeach
                    <div class="account_price" >
                        @if ( $account[ 'price' ][ 'discount' ] == true )
                            <span class="account_old_price">{{ __( 'base.account_confirmation_' . $account[ 'id' ] . '_old_price' ) . ' ' . __( 'base.account_confirmation_currency' ) }}<br></span>
                        @endif
                            <span class="account_new_price">{{ __( 'base.account_confirmation_' . $account[ 'id' ] . '_new_price' ) . ' ' . __( 'base.account_confirmation_currency' ) }}</span>
                    </div>
            </div>
        @endforeach
        </div>
        <div id="button_errors_container">{{ $accountData[ 'errors' ][ 'account_choice' ]}}</div>
    <div class="account_choice_button_container"><button type="submit" class="account_choice_button">{{ __( 'base.button_account_choice' ) }}</button></div>
    </form>
</div>
@endsection

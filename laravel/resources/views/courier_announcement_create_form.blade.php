@extends('layouts.app')

@section('add_header')
    <script src="{{ asset('js/courier_announcement_scripts.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/courier_announcement_styles.css') }}">
    @php
        $JsonParserController = app(\App\Http\Controllers\JsonParserController::class);
        $directionsData = $JsonParserController->directionsAction();
        $cargoData = $JsonParserController->cargoAction();
    @endphp
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('base.courier_announcement_create_form_title') }}</div>
                        <div class="card-body">
                            <form action="{{ route('courier_announcement.generateCourierAnnouncement') }}" method="POST" id="courier_announcement_form">
                                @csrf

                                <x-input_form_component name="courier_announcement_name" type="text" />
                                <small id="courier_announcement_name_info" class="form-text text-muted">{{ __( 'base.courier_announcement_name_info' ) }}</small>
                                <div class="cargo_type_container">
                                    <div class="col-md-14"></div>
                                    <table class="table border border-1">
                                        <thead>
                                            <tr>
                                                <th colspan="5" class="text-center border-1"><p class="h3 text-center">{{ __('base.courier_announcement_type_cargo_title')}}</p></th>
                                            </tr>
                                            <tr class="text-center">
                                                <th class="col-md-1" scope="col">{{ __( 'base.courier_announcement_cargo_type_id' ) }}</th>
                                                <th class="col-md-2" scope="col">{{ __( 'base.courier_announcement_cargo_type_name' ) }}</th>
                                                <th scope="col">{{ __( 'base.courier_announcement_cargo_type_description' ) }}</th>
                                                <th class="col-md-2" scope="col">{{ __( 'base.courier_announcement_cargo_type_price' ) }}</th>
                                                <th class="col-md-1" scope="col">{{ __( 'base.courier_announcement_cargo_type_actions' ) }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{ $id = 1 }}
                                            <div class="component_container">
                                                <x-input_form_component id="{{ $id }}" />
                                                {{-- <tr class="align-middle h-100">
                                                    <th scope="row" class="h3">{{ $id }}</th>
                                                    <td>
                                                        <input id="cargo_name_{{ $id }}" type="text" class="form-control" name="cargo_name_{{ $id }}" required autocomplete="cargo_name_{{ $id }}">
                                                        <small id="courier_announcement_name_info" class="cargo_name_message_{{ $id }}">{{ __( 'base.courier_announcement_cargo_required_message' ) }}</small>
                                                    </td>
                                                    <td>

                                                    </td>
                                                    <td>

                                                    </td>
                                                    <td>

                                                    </td>

                                                </tr> --}}
                                            </div>
                                            <div>
                                                <table>
                                                    <tr>
                                                        <td colspan="5">
                                                            <div class="d-flex align-items-center justify-content-between cargo_type_button_container">
                                                                <div class="add_new_cargo_type_button">
                                                                    <button class="btn btn-primary add_cargo_component_btn" type="button">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus-square" viewBox="0 0 16 16">
                                                                            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                                                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                                        </svg> {{ __( 'base.courier_announcement_cargo_type_button_add' ) }}
                                                                    </button>
                                                                </div>
                                                                <div class="warning_text">
                                                                    <p>{{ __( 'base.courier_announcement_cargo_type_add_info' ) }}</p>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>

                                        </tbody>
                                    </table>
                                </div>
                                {{-- <div class="col-md-14 border-bottom"></div>

                                <div class="col-md-14 border-bottom"></div>
                                <x-input_form_component name="direction" type="select" :options="$directionsData" />
                                <x-input_form_component name="post_code_sending" type="text" />
                                <x-input_form_component name="post_code_receiving" type="text" />
                                <x-input_form_component name="phone_number" type="text" />
                                <x-input_form_component name="email" type="text" />
                                <x-input_form_component name="expect_sending_date" type="date" />
                                <x-input_form_component name="experience_date" type="date" />
                                <div class="col-md-14 border-bottom"></div>
                                <div class="row mb-0">
                                    <div class="text-md-end">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('base.next') }}
                                        </button>
                                    </div>
                                </div> --}}

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

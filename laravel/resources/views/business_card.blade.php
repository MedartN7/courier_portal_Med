@extends('layouts.app')

@section('add_header')
    <link rel="stylesheet" href="{{ asset('css/welcome_styles.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 mx-auto">
            <div class="card text-center">
                <div class="card-header">{{ __('WIZYTÓWKI') }}</div>
                <div class="card-body d-flex flex-wrap justify-content-center">
                    @foreach ($settings['elements'] as $element)
                        <x-business-card-component :element="$element" class="m-2" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

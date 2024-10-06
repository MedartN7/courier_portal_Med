@extends('layouts.app')

@section('add_header')

@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card text-center">
                <div class="card-header">{{ __('base.donate_title') }}</div>
                <div class="card-body">  
                    <p><strong> </strong></p>
                    <form action="https://www.paypal.com/donate" method="post" target="_top">
                        <p><strong>{{ __('base.donate_body') }}</strong></p>
                        <input type="hidden" name="hosted_button_id" value="9TCRD67J2FJQA" />
                        <input type="image" src="https://www.paypalobjects.com/en_US/GB/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
                        <img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
                    </form>
                    <br>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountTypeController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function create( Request $request )
    {
        $validator = $this->validator( $request->all() );
        if ($validator->fails()) {
            return redirect()->route('confirmed_account_get', $request->all() )->withErrors( $validator )->withInput();
        } else {
            return redirect()->route('confirmed_account_last' );
        }
    }

    private function validator(array $data)
    {
        $validate_result = Validator::make($data, [
            'name' => ['required', 'string', 'max:55'],
            'surname' => ['required', 'string', 'max:55'],
            'phone_number' => ['required', 'numeric', 'digits_between:9,14'],
            'd_o_b' => ['required', 'date', 'before:2010-01-01'],
        ]);

        if ( $data['name'] == 'account_type' ) {
            $company_validate_result = Validator::make($data, [
                'company_name' => ['required', 'string', 'max:99'],
                'company_address' => ['required', 'string', 'max:55'],
                'company_phone_number' => ['required', 'numeric', 'min:9', 'max:14'],
                'company_post_code' => ['required', 'string', 'max:9'],
                'company_city' => ['required', 'string', 'max:44'],
                'company_country' => ['required', 'string', 'max:44'],
            ]);

            $validate_result->merge( $company_validate_result );
        }

        return $this->setAttributesFormNames( $validate_result );
    }

    private function setAttributesFormNames( $data ) {
        $data->setAttributeNames([
            'name' => __('base.name'),
            'surname' => __('base.surname'),
            'phone_number' => __('base.phone_number'),
            'company_name' => __('base.company_name'),
            'company_address' => __('base.company_address'),
            'company_phone_number' => __('base.company_phone_number'),
            'company_post_code' => __('base.company_post_code'),
            'company_city' => __('base.company_city'),
            'company_country' => __('base.company_country'),
            'd_o_b' => __('base.d_o_b'),
        ]);

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class ContactController extends Controller
{
    public function sendMail( Request $request )
    {
        //dd( $request );
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);
        $data = [
            'subject' => $validated['name'],
            'first_name' => $validated['name'],
            'last_name' => $validated['surname'] ?? '', 
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '',
            'message_content' => $validated['message'],
        ];


        Mail::send('emails.contact', $data, function($message) use ($data) {
            $message->to('qqla83@gmail.com')
                    ->subject($data['subject']); 
        });

        
        return view('redirection_info')
            ->with('id', 'send_form_confirmation')
            ->with('title', __( 'base.contact_confirm_label' ) )
            ->with('infoAnnouncement', __('base.contact_confirm_info') )
            ->with('redirectionRouteName', 'main')
            ->with('redirectedText', __('base.contact_redirected_text') )
            ->with('delayTime', 5000 );
    }

    public function getContactFormData( Request $request ) {
        $json = new JsonParserController;
        $regularExpression = $json->getRegularExpression();  
        return view( 'contact', [ 'json' => $regularExpression ] );
    }

    
    
    // private function getMessageInput( $name ) {
    //     //dd( $regularExpression, $name );
    //     echo( $name . $regularExpression );
    //     if ( isset( $regularExpression[ $name ] ) && !empty( $regularExpression[ $name ] ) ) {
    //         return  __( 'base.' . $regularExpression[ $name ][ 'message' ] );
    //     }
    //     return null;
    // }

    // private function getRegexInput( $name ) {
    //     if ( isset( $regularExpression[ $name ] ) && !empty( $regularExpression[ $name ] ) ) {
    //         return $regularExpression[ $name ][ 'regex' ];
    //     }
    //     return null;
    // }
}
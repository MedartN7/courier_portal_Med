<?php

require base_path('vendor' . DIRECTORY_SEPARATOR . 'mgs' . DIRECTORY_SEPARATOR . 'confirm_access' . DIRECTORY_SEPARATOR . 'ConfirmAccessExtension' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'routes.php');
require base_path('vendor' . DIRECTORY_SEPARATOR . 'mgs' . DIRECTORY_SEPARATOR . 'change_password' . DIRECTORY_SEPARATOR . 'ChangePasswordExtension' . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'routes.php');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeclarationPrintController;
use App\Http\Controllers\UserAnnouncementController;
use App\Http\Controllers\CourierAnnouncementController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CustomUserController;
use App\Http\Controllers\JsonParserController;
use App\Http\Controllers\CookiesController;
use App\Http\Controllers\BusinessCardController;
use App\Http\Controllers\GoogleAuthController;

//debug_print_backtrace(); exit();

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
{ //############################### BASIC ##############################
    Auth::routes( ['verify' => true] );

    Route::get('/', function () { return view('welcome'); } )->name('main');
    Route::get('/rodo/rules', function () { return view('rodo'); })->name('rodo');
    Route::get('/policy/rules', function () { return view('rodo'); })->name('policy');
    Route::get('/donate', function () { return view('donations'); })->name('donate');
    Route::get('/business/card', [ BusinessCardController::class, 'show' ] )->name('businessCard');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/contact', function () { return view('contact'); } )->name('contact');
    Route::get('/contact/data', [ App\Http\Controllers\ContactController::class, 'getContactFormData' ] )->name('contactData');
    Route::get('/help', function () { return view('help'); } )->name('help');

    Route::get('/no_access', function () { return view('no_access'); })->name('no_access')->middleware( ['auth', 'verified'] );
    Route::post('/sendMail', [App\Http\Controllers\ContactController::class, 'sendMail'] )->name('sendMail');
    Route::get('/cookies/moreInfo', function () { return view('cookies_more_info'); } )->name('cookiesMore');

} //####################################################################


{ //############################### USER ACCOUNT #######################
    Route::get('user_edit_profile', [ CustomUserController::class, 'edit'] )
        ->name('user_edit_profile')
        ->middleware( ['auth', 'verified'] );

    Route::post('user_update_profile', [ CustomUserController::class, 'update'] )
        ->name('user_update_profile')
        ->middleware( ['auth', 'verified'] );

    Route::delete('user/profile/destroy{id}', [ CustomUserController::class, 'destroy'] )
        ->name('userDestroy')
        ->middleware( ['auth', 'verified'] );

    Route::get('user/profile/destroy/confirm', [ CustomUserController::class, 'confirmedDestroy'] )
        ->name('confirmDestroy')
        ->middleware( ['auth', 'verified'] );

    Route::get('user/profile', function () { return view('user_profile'); })->name('profile');

    Route::get('user_edit_summary', [ CustomUserController::class, 'editUserSummary'] )
        ->name('user_edit_summary')
        ->middleware( ['auth', 'verified'] );
} //####################################################################


{ //############################### DECLARATION ########################
    Route::get('/cn22', function () { return view('cn22'); })
        ->name('cn22')
        ->middleware( ['auth', 'verified'] );

    Route::get('/cn23', function () { return view('cn23'); })
        ->name('cn23')
        ->middleware( ['auth', 'verified'] );

    Route::post('/pdf_gen', function (Request $request) {
        $postData = $request->all();
        $pdf = app()->makeWith(DeclarationPrintController::class, ['post' => $postData]);
        return $pdf->generatePDFDocument(); })
            ->name('pdf_gen')
            ->middleware( ['auth', 'verified'] ); // nie wiem czy bedzie działać trzeba przetestować
} //####################################################################


{ //############################### ACCOUNT TYPES ######################
    Route::get('register_account', [ AccountController::class, 'registerAccount'] )
        ->middleware(['auth', 'verified'])
        ->name('register_account');

    Route::get('accounts/confirmed_account', [ AccountController::class, 'confirmAccountType'] )
        ->name('confirmed_account')
        ->middleware( ['auth', 'verified'] );

    Route::post('add_account_type_and_user_details', [ AccountController::class, 'store'] )
        ->name('add_account_type_and_user_details')
        ->middleware( ['auth', 'verified'] );

    Route::get('edit_type_account', [ AccountController::class, 'edit'] )
        ->middleware(['auth', 'verified'])
        ->name('edit_type_account');

    Route::post('accounts/confirm_edit_account', [ AccountController::class, 'update'] )
        ->name('confirm_edit_account')
        ->middleware( ['auth', 'verified'] );

    Route::get('/accounts/edit_account_confirm_last', function () { return view('accounts.edit_account_confirm_last'); })
        ->name('edit_account_confirm_last')
        ->middleware( ['auth', 'verified'] );
} //####################################################################


{ //############################### USER ANNOUNCEMENT ##################
    Route::resource('user_announcement', UserAnnouncementController::class)
        ->middleware(['auth', 'verified', 'account_check:courier,courier_pro,standard,standard_pro'])->except(['show', 'index']);
    Route::get('user_announcement/show/single/{id}', [ UserAnnouncementController::class, 'showSingle'] )
        ->name('user_announcement_show_single');
    Route::get('user_announcement/index', [ UserAnnouncementController::class, 'index'] )
        ->name('user_announcement.index');
    Route::get('generate_user_announcement', [ UserAnnouncementController::class, 'create'] )
        ->middleware( ['auth', 'verified', 'account_check:courier,courier_pro,standard,standard_pro'] )
        ->name('generate_user_announcement');
    Route::post('cargo_generator', [UserAnnouncementController::class, 'cargoDataGenerator'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,standard,standard_pro'])
        ->name('cargo_generator');
    Route::get('user_announcements_list', [UserAnnouncementController::class, 'indexForSingleUser'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,standard,standard_pro'])
        ->name('user_announcements_list');
    Route::post('announcement_confirm_destroy/{id}', [UserAnnouncementController::class, 'destroyConfirm'])
        ->middleware(['auth', 'verified', 'account_check:courier,courier_pro,standard,standard_pro'])
        ->name('announcement_confirm_destroy');
    Route::post('user_announcement_summary', [UserAnnouncementController::class, 'summary'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,standard,standard_pro'])
        ->name('user_announcement_summary');
    Route::get('user_announcement.searchFiltersSummary', [UserAnnouncementController::class, 'searchFiltersSummary'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,standard,standard_pro'])
        ->name('user_announcement.searchFiltersSummary');
} //####################################################################


{ //############################### COURIER ANNOUNCEMENT ##################
    Route::resource('courier_announcement', CourierAnnouncementController::class)
        ->middleware(['auth', 'verified', 'account_check:courier_pro,courier,standard_pro'])
        ->except(['create', 'show']);

    Route::get('courier_announcement', [CourierAnnouncementController::class, 'index'])
        ->name('courier_announcement.index');
    Route::get('courier_announcement/{id}', [CourierAnnouncementController::class, 'show'])
        ->name('courier_announcement.show');
    Route::match(['get', 'post'], 'create_courier_announcement', [CourierAnnouncementController::class, 'create'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,courier'])
        ->name('courier_announcement.create');
    Route::post('courier_announcement_generator', [CourierAnnouncementController::class, 'generateCourierAnnouncement'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,courier'])
        ->name('courier_announcement_generator');
    Route::post('courier_announcement_summary', [CourierAnnouncementController::class, 'summary'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,courier'])
        ->name('courier_announcement.summary');
    Route::post('courier_announcement_update', [CourierAnnouncementController::class, 'updateEdit'])
        ->middleware(['auth', 'verified', 'account_check:courier_pro,courier'])
        ->name('courier_announcement.updateEdit');
    Route::match(['get', 'post'], 'courier_announcement.searchFiltersSummary', [CourierAnnouncementController::class, 'searchFiltersSummary'])
    ->middleware(['auth', 'verified', 'account_check:courier_pro,courier'])
    ->name('courier_announcement.searchFiltersSummary');
    Route::get('courier_announcement_user_list', [CourierAnnouncementController::class, 'indexForSingleUser'])
        ->middleware(['auth', 'verified', 'account_check:courier,courier_pro,standard,standard_pro'])
        ->name('courier_announcement_user_list');
} //####################################################################


{ //############################### END POINTS #########################
    // Route::get('/settings/regex', [ JsonParserController::class, 'getRegularExpression']);
    Route::get( '/cookies/translate', [ CookiesController::class, 'getTranslations'] );
} //####################################################################



{ //############################### SOCIAL MEDIA #######################
    Route::get('/google/auth', [ GoogleAuthController::class, 'redirectToGoogle'] )->name( 'googleLogin' );
    Route::get( '/google/auth/callback', [ GoogleAuthController::class, 'handleGoogleCallback'] )->name( 'googleCallback' );
} //####################################################################
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use App\Models\UserModel;

use App\Http\Controllers\JsonParserController;

use App\Models\CourierAnnouncement;
use App\Models\CargoTypes;
use App\Models\CourierAnnouncementImages;
use App\Models\CourierTravelDate;
use App\Models\PostCodePl;
use App\Models\PostCodeUk;
use App\Models\CourierAnnouncementContact;
use App\Models\CourierAnnouncementAdditionalDirections;

use App\Models\CourierAnnouncementArchive;
use App\Models\CargoTypesArchive;
use App\Models\CourierAnnouncementImagesArchive;
use App\Models\CourierTravelDateArchive;
use App\Models\PostCodePlArchive;
use App\Models\PostCodeUkArchive;
use App\Models\CourierAnnouncementContactArchive;
use App\Models\CourierAnnouncementAdditionalDirectionsArchive;

class CourierAnnouncementController extends Controller
{

    public function __construct() {
        $this->json = new JsonParserController;
    }

    public function generateCourierAnnouncement( Request $request ) {
        $tempFilePath = $this->generateImagesTempFilesPath();
        $this->generateTempFolderIfDontExist();
        $this->saveImagesFilesInTempFolder( $request->file('files'), $tempFilePath );
        return $this->summary( $request );
    }

    public function index() {
        $query = CourierAnnouncement::with( [
            'cargoTypeAnnouncement',
            'imageAnnouncement',
            'dateAnnouncement',
            'postCodesPlAnnouncement',
            'postCodesUkAnnouncement',
            'contactAnnouncement',
            'additionalPostCodes'
        ] );
        $announcementTitles = $this->generateAnnouncementTitlesInList($query->get());
        $perPage = $this->json->courierAnnouncementAction()['number_of_search_courier_announcement_in_one_page'];
        $announcements = $query->paginate($perPage);

        $view = view('courier_announcement_list', [
            'announcements' => $announcements,
            'announcementTitles' => $announcementTitles,
        ]);

        return $view;
    }

    private function generateDeliveryDates( $request ) {
        $dates = [];

        for( $i = 1; $i <= $request->input( 'date_number_visible' ); $i++ ) {
            $from = __( 'base.courier_announcement_date_direction_name_from' ) .
                    ' ' .
                    __( 'base.direction_print_full_name_genitive_' .
                    $request->input( 'from_date_directions_select_' . $i ) );
            $to = __( 'base.courier_announcement_date_direction_name_to' ) .
                  ' ' .
                  __( 'base.direction_print_full_name_genitive_' .
                  $request->input( 'to_date_directions_select_' . $i ) );
            $date = $request->input( 'date_input_' . $i );
            $description = $request->input( 'date_description_' . $i ) != null ? ' ( ' . $request->input( 'date_description_' . $i )  . ' )' : '';
            $singleDate = $date . ' ' . $from . ' ' . $to  .  $description;
            $dates[] = $singleDate;
        }
        return $dates;
    }

    public function summary( Request $request ) {
        $prevImagesLinks = $this->generatePrevLinksArrayForEdit( $request );
        $prevImagesLinksSummary = $this->generatePrevLinksArrayForSummary( $request );
        $imagesLinks = $this->generateLinksForImages( $request->file('files'), $this->generateImagesTempFilesPath( '/' ), count( $prevImagesLinks ) );
        $allImagesLinks = array_merge( $imagesLinks, $prevImagesLinksSummary );
        $allOldImagesLinks = array_merge( $imagesLinks, $prevImagesLinks );
        $request->merge( ['all_pictures_number' => '' . count( $allImagesLinks ) ] );
        $request->session()->flashInput( $request->input() ); // zamiana post na sesje
        $request->session()->forget('files');
        $request->flash();

        $this->validateAllRequestData( $request);
        $this->generateImagesFolderIfDontExist();
        $countryData = $this->generateDataForDeliveryCountryToSession();
        $company = UserModel::with('company')->find( auth()->user()->id );
        $summaryTitle = $this->generateSummaryAnnouncementTitle( $request, $company );
        //dd( $allImagesLinks, $allOldImagesLinks );
        $allPostCodesSummary = $this->generateAllPostCodesSummary( $request );
        $directions = $this->json->directionsAction();
        $contactData = $this->generateContactData( $request );
        $deliveryDates = $this->generateDeliveryDates( $request );
        return view( 'courier_announcement_summary' )
                        ->with( 'title', $summaryTitle )
                        ->with( 'countryPostCodesData', $countryData)
                        ->with( 'userAndCompany', $company )
                        ->with( 'directions', $directions )
                        ->with( 'imagesLinks', $allImagesLinks )
                        ->with( 'oldImagesLinks', $allOldImagesLinks )
                        ->with( 'postCodes', $allPostCodesSummary )
                        ->with( 'contactData', $contactData )
                        ->with( 'deliveryDates', $deliveryDates );
    }

    // private function validateDirectionsField( $request ) {
    //     $validator = Validator::make($request->all(), [
    //         'postfix_select_post_code_sending' =>                        [ 'required', 'max:6' ],
    //         'direction_city_post_code_sending' =>                        [ 'required', 'max:80' ],
    //         'postfix_select_post_code_receiving' =>                      [ 'required', 'max:6' ],
    //         'direction_city_post_code_receiving' =>                      [ 'required', 'max:80' ],
    //     ], $this->generateErrorsMessages() );
    //     return $validator;
    // }

    public function addAnnouncementConfirmation() {
        return view( 'courier_announcement_confirmation_info' );
    }

    public function addAnnouncementEditConfirmation() {
        return view( 'courier_announcement_edit_confirmation_info' );
    }

    public function addAnnouncementDeleteConfirmation() {
        return view( 'courier_announcement_delete_confirmation_info' );
    }

    public function create( Request $request ) {
        //dd( $request );

        $company = UserModel::with('company')->find( auth()->user()->id );
        $extensions = $this->generateAcceptedFileFormatForCreateBlade();
        $contactData = $this->generateDataForContact( $company );
        $headerData = $this->generateCourierAnnouncementCreateFormHeader();
        $directionsData = $this->json->directionsAction();
        $cargoData = $this->json->cargoAction();
        return view( 'courier_announcement_create_form' )
            ->with( 'extensions', $extensions )
            ->with( 'contactData', $contactData )
            ->with( 'headerData', $headerData )
            ->with( 'directionsData', $directionsData )
            ->with( 'cargoData', $cargoData );
    }

    public function store(Request $request) {
        // dd( $request );
        //$data = $request->all();
        // dd( 'store()', $request);
        // $postCodes = $this->generateDirectionsPostcodesArray();
        // dd( $postCodes );
        // $this->storeImages( $data, 66 );
        $courierAnnouncement = new CourierAnnouncement( [
            'name' =>                   $request->input( 'courier_announcement_name' ),
            'description' =>            $request->input( 'additional_description_input' ),
            'experience_date' =>        $this->getExperienceAnnouncementDate(
                                            $request->input( 'experience_announcement_date_input' ),
                                            $request->input( 'experience_for_premium_date' )
                                        ),
        ] );
        $userId = auth()->id();
        $courierAnnouncement->authorUser()->associate( $userId );
        $courierAnnouncement->save();
        $this->storeContactData( $request, $courierAnnouncement->id );
        $this->storeImages( $request, $courierAnnouncement->id );
        $this->storeCargos( $request, $courierAnnouncement->id );
        $this->storeDates( $request, $courierAnnouncement->id );
        $this->storeAllPostCodes( $request, $courierAnnouncement->id );

        return $this->addAnnouncementConfirmation();
    }

    // private function flattenArray($array) {
    //     $result = [];
    //     dd($array);
    //     foreach ( $array as $key => $value ) {
    //         if ( is_array($value) ) {
    //             $result = array_merge($result, $this->flattenArray( $value, $key . '_' ) );
    //         } else {
    //             $result[ $key ] = $value;
    //         }
    //     }
    //     dd($result);
    //     return $result;
    // }

    private function generateDataForEdit( $id ) {
        $baseAnnouncementArray = [];
        $announcementObject = $this->getCourierAnnouncementWithRelations( $id );
        $announcement = $announcementObject->toArray();
        // $announcement = $fullAnnouncementData->toArray();

        $baseAnnouncementArray[ 'courier_announcement_name' ] = $announcement[ 'name' ];
        $baseAnnouncementArray[ 'author' ] = $announcement[ 'author' ];
        $baseAnnouncementArray[ 'description' ] = $announcement[ 'description' ];
        $baseAnnouncementArray[ 'experience_date' ] = $announcement[ 'experience_date' ];

        $baseAnnouncementArray[ 'experience_announcement_date_input' ] = $announcement[ 'experience_date' ];
        if ( $announcement[ 'experience_date' ] == null  ) {
            $baseAnnouncementArray[ 'experience_for_premium_date' ] = true;
        }
        $baseAnnouncementArray[ 'cargo_number_visible' ] = count( $announcement[ 'cargo_type_announcement' ] );
        $baseAnnouncementArray[ 'date_number_visible' ] = count( $announcement[ 'date_announcement' ] );

        $imagesNumber = count( $announcement[ 'image_announcement' ] );
        $baseAnnouncementArray[ 'old_summary_images_number' ] = $imagesNumber;
        $baseAnnouncementArray[ 'images_number' ] = $imagesNumber;
        $baseAnnouncementArray[ 'old_images_number' ] = $imagesNumber;

        $cargoArray = $this->generateDataForEditCargo( $announcement[ 'cargo_type_announcement' ] );
        $imageArray = $this->generateDataForEditImage( $announcement[ 'image_announcement' ] );
        $dateArray = $this->generateDataForEditDate( $announcement[ 'date_announcement' ] );
        $postCodesArray = $this->generateDataForEditPostCode( $announcement );
        $contactArray = $this->generateDataForEditContact( $announcement[ 'contact_announcement' ] );
        $additionalPostCodesArray = $this->generateDataForEditAdditionalPostCode( $announcement[ 'additional_post_codes' ] );

         //dd( $baseAnnouncementArray, $announcement );
        return array_merge( $baseAnnouncementArray, $cargoArray, $imageArray, $dateArray, $postCodesArray, $contactArray, $additionalPostCodesArray );
    }

    private function generateDataForEditCargo( $data ) {
        $cargoArray = [];

        foreach( $data as $key => $value ) {
            $cargoArray[ 'cargo_name_' . ( $key + 1 ) ] = $value[ 'cargo_name' ];
            $cargoArray[ 'cargo_description_' . ( $key + 1 ) ] = $value[ 'cargo_description' ];
            $cargoArray[ 'cargo_price_' . ( $key + 1 ) ] = $value[ 'cargo_price' ];
            $cargoArray[ 'select_currency_' . ( $key + 1 ) ] = $value[ 'currency' ];
        }

        return $cargoArray;
    }

    private function generateDataForEditImage( $data ) {
        $imageArray = [];

        foreach( $data as $key => $value ) {
            $imageArray[ 'image' . ( $key + 1 ) ] = $value[ 'image_link' ];
        }

        return $imageArray;
    }

    private function generateDataForEditDate( $data ) {
        $dateArray = [];

        foreach( $data as $key => $value ) {
            $dateArray[ 'from_date_directions_select_' . ( $key + 1 ) ] = $value[ 'dir_from' ];
            $dateArray[ 'to_date_directions_select_' . ( $key + 1 ) ] = $value[ 'dir_to' ];
            $dateArray[ 'date_input_' . ( $key + 1 ) ] = $value[ 'date' ];
            $dateArray[ 'date_description_' . ( $key + 1 ) ] = $value[ 'description' ];
        }

        return $dateArray;
    }

    private function generateDataForEditPostCode( $data ) {
        $postCodeArray = [];
        $directions = $this->json->directionsAction();
        foreach( $directions as $dir ) {
            $direction = $dir[ 'name' ];
            $directionPostCodes = $this->json->getPostCodes( $direction );

            foreach( $directionPostCodes as $postCode ) {
                $postCodeArray[ $postCode ] = $data[ 'post_codes_' . $direction . '_announcement' ][ 0 ][ $postCode ];
            }
        }
        return $postCodeArray;
    }

    private function generateDataForEditContact( $data ) {
        $contactArray = [];

        $contactArray[ 'contact_detail_name' ] = $data[ 'name' ];
        $contactArray[ 'contact_detail_surname' ] = $data[ 'surname' ];
        $contactArray[ 'contact_detail_company' ] = $data[ 'company' ];
        $contactArray[ 'contact_detail_street' ] = $data[ 'street' ];
        $contactArray[ 'contact_detail_city' ] = $data[ 'city' ];
        $contactArray[ 'contact_detail_post_code' ] = $data[ 'post_code' ];
        $contactArray[ 'contact_detail_country' ] = $data[ 'country' ];
        $contactArray[ 'contact_detail_telephone_number' ] = $data[ 'telephone_number' ];
        $contactArray[ 'contact_detail_additional_telephone_number' ] = $data[ 'additional_telephone_number' ];
        $contactArray[ 'contact_detail_email' ] = $data[ 'email' ];
        $contactArray[ 'contact_detail_website' ] = $data[ 'website' ];
        return $contactArray;
    }

    private function generateDataForEditRequest( $editData ) {
        $requestArray = [];

        $requestArray[ 'old_summary_images_number' ] = $editData[ 'old_summary_images_number' ];
        for( $i = 1; $i <= $requestArray[ 'old_summary_images_number' ]; $i++ ) {
            $requestArray[ 'image' . $i ] = $editData[ 'image' . $i ];
        }

        for( $i = 1; $i <= $requestArray[ 'old_summary_images_number' ]; $i++ ) {
            $requestArray[ 'old_image_' . $i ] = $editData[ 'image' . $i ];
        }

        return $requestArray;
    }

    private function generateDataForEditAdditionalPostCode( $data ) {
        $additionalPostCodeArray = [];

        return $additionalPostCodeArray;
    }

    private function getCourierAnnouncementWithRelations( $id ) {
        $announcement = CourierAnnouncement::with( [
            'cargoTypeAnnouncement',
            'imageAnnouncement',
            'dateAnnouncement',
            'postCodesPlAnnouncement',
            'postCodesUkAnnouncement',
            'contactAnnouncement',
            'additionalPostCodes'
        ] )->findOrFail( $id );

        //$this->generateDataForEdit( $announcement );



        return $announcement;
    }

    private function getExperienceAnnouncementDate( $date, $checkboxDate ) {
        if ( $checkboxDate !== null ) {
            return null;
        }
        return $date;
    }

    private function storeContactData( Request $request, $announcementID ) {
        $contactArray =  $this->generateContactData( $request );

        $date = new CourierAnnouncementContact ( [] );
        foreach ( $contactArray as $key => $value ) {
            $date->{$key} = $value;
        }

        $date->announcementId()->associate( $announcementID );
        $date->save();
    }

    private function storeImages( $request, $announcementID ) {

        $filePath = $this->generateImagesFilesPath();
        $tempPath = $this->generateImagesTempFilesPath();
        //dd( $request );
        for( $i = 1; $i <= $request->input( 'old_summary_images_number' ); $i++ ) {
            if ( $request->input( 'old_image_info_' . $i ) == 'isForDelete' ) {
                //dd( $i );
                continue;
            }
            //dd( $i );
            $path = $request->input( 'image' . $i );
            $fileName = basename( $path );
            $newFilePath = $path;
            if ( Str::startsWith( $path , '//storage//') ) {
                $oldFilePath = $tempPath . DIRECTORY_SEPARATOR . $fileName;
                $newFilePath = $filePath . DIRECTORY_SEPARATOR . $fileName;
                $isSuccessStoreFile = Storage::move( $oldFilePath, $newFilePath );
                if( !$isSuccessStoreFile ) {
                    dd( $isSuccessStoreFile, 'ERROR storeImages() in CourierAnnouncementController', ltrim($tempPath, '\\'), $newFilePath );
                }
            }

            $images = new CourierAnnouncementImages ( [
                'image_name' =>                      $fileName,
                'image_link' =>                      $newFilePath,
            ] );
            $images->announcementId()->associate( $announcementID );
            $images->save();
            // if( $i > 2){
            //     dd('tu');
            // }
        }
    }

    private function checkIfLastCargoIsEmpty( Request $request, $lastIndex ) {
        $cargoName = $request->input( 'cargo_name_' . $lastIndex );
        $cargoPrice = $request->input( 'cargo_price_' . $lastIndex );
        $cargoDescription = $request->input( 'cargo_description_' . $lastIndex );
        $cargoCurrency = $request->input( 'select_currency_' . $lastIndex );

        if ( $cargoName === null && $cargoPrice === null && $cargoDescription === null && $cargoCurrency === null ) {
            return true;
        }

        return false;
    }

    private function checkIfLastDateIsEmpty( Request $request, $lastIndex ) {
        $direction = $request->input( 'date_directions_select_' . $lastIndex );
        $date = $request->input( 'date_input_' . $lastIndex );
        $description = $request->input( 'date_description_' . $lastIndex );

        if ( $direction === null && $date === null && $description === null ) {
            return true;
        }

        return false;
    }

    private function storeCargos( Request $request, $announcementID ) {
        $cargoElementsNumber = $request->input( 'cargo_number_visible' );
        if ( $this->checkIfLastCargoIsEmpty( $request, $cargoElementsNumber ) ) {
            $cargoElementsNumber--;
        }

        for ( $i = 1; $i <= $cargoElementsNumber; $i++ ) {

            $cargo = new CargoTypes ( [
                'cargo_name' =>                      $request->input( 'cargo_name_' . $i ),
                'cargo_price' =>                     $request->input( 'cargo_price_' . $i ),
                'cargo_description' =>               $request->input( 'cargo_description_' . $i ),
                'currency' =>                        $request->input( 'select_currency_' . $i ),
            ] );
            $cargo->announcementId()->associate( $announcementID );
            $cargo->save();
        }
    }

    private function storeDates( Request $request, $announcementID ) {
        $dateElementsNumber = $request->input( 'date_number_visible' );
        if ( $this->checkIfLastDateIsEmpty( $request, $dateElementsNumber ) ) {
            $dateElementsNumber--;
        }

        for ( $i = 1; $i <= $dateElementsNumber; $i++ ) {
            $date = new CourierTravelDate ( [
                'dir_from' =>                      $request->input( 'from_date_directions_select_' . $i ),
                'dir_to' =>                        $request->input( 'to_date_directions_select_' . $i ),
                // tutaj dodac czy w danej dacie sa dodatkowe postkody jezeli takowe sie pojawia
                // 'additional_dir' =>                $request->input( 'date_directions_select_' . $i ),
                'date' =>                          $request->input( 'date_input_' . $i ),
                'description' =>                   $request->input( 'date_description_' . $i ),
            ] );
            $date->announcementId()->associate( $announcementID );
            $date->save();
            // umiescic tutaj funkcje zapisywania do bazy dodatkowych postkodow jezeli takowe sie pojawia
        }
    }

    private function storeAllPostCodes( $request, $announcementID ) {
        $allPostCodes = $this->generateDirectionsPostcodesArray();
        // w tej funkcji umiescic dodatkowy case jezeli pojawia sie dodatkowe kierunki
        foreach( $allPostCodes as $key => $postCodesArray ) {
            switch ( $key ) {
                case 'pl':
                    $this->storePostCodesPL( $request, $postCodesArray, $announcementID );
                    break;
                case 'uk':
                    $this->storePostCodesUK( $request, $postCodesArray, $announcementID );
                    break;
                default:
                    dd( 'ERROR in storeAllPostCodes()');
                    break;
            }
        }
    }

    private function storePostCodesPL( $request, $postCodesArray, $announcementID ) {
        $postCodesArray = [];
        foreach ( $this->json->plPostCodeAction() as $postCode ) {
            if ( $request->input( $postCode ) === $postCode ) {
                $postCodesArray[ $postCode ] = 1;
            } else {
                $postCodesArray[ $postCode ] = 0;
            }
        }

        $postCodesPL = new PostCodePl ( $postCodesArray );
        $postCodesPL->announcementId()->associate( $announcementID );
        $postCodesPL->save();
    }

    private function storePostCodesUK( $request, $postCodesArray, $announcementID ) {
        $postCodesArray = [];
        foreach ( $this->json->ukPostCodeAction() as $postCode ) {
            if ( $request->input( $postCode ) === $postCode ) {
                $postCodesArray[ $postCode ] = 1;
            } else {
                $postCodesArray[ $postCode ] = 0;
            }
        }
        $postCodesUK = new PostCodeUk ( $postCodesArray );
        $postCodesUK->announcementId()->associate( $announcementID );
        $postCodesUK->save();
    }

    public function show( string $id ) {
        $courierAnnouncement = $this->getCourierAnnouncementWithRelations( $id );
        $allPostCodes = $this->generatePostCodesFromDataBase( $courierAnnouncement );
        $announcementTitle = $this->generateAnnouncementTitlesInList( [ $courierAnnouncement ] );
        $cargo = $courierAnnouncement->cargoTypeAnnouncement;
        $dates = $courierAnnouncement->dateAnnouncement;
        $images= $this->getImagesLinks( $courierAnnouncement->imageAnnouncement );
        $readyDates = $this->generateDates( $dates );

        return view( 'courier_announcement_single_show', [] )
                    ->with( 'announcement', $courierAnnouncement->first() )
                    ->with( 'announcementTitle', $announcementTitle[0] )
                    ->with( 'cargo', $cargo )
                    ->with( 'dates', $readyDates )
                    ->with( 'postCodes', $allPostCodes )
                    ->with( 'images', $images );
    }

    // private function getPostCodesFromDataBase( $data, $postCodesJson ) {
    //     $postCodeArray = [];

    //     if( count($data) === 0 ) {
    //         return [];
    //     }

    //     foreach( $postCodesJson as $postCode ) {
    //         if( $data[0][$postCode] === 1 ) {
    //             $postCodeArray[ $postCode ] = $postCode;
    //         }
    //     }
    //     return $postCodeArray;
    // }

    // private function getPostCodesAndDirections( $courierAnnouncement ) {
    //     $array = [];
    //     $courierAnnouncementJson = $this->json->courierAnnouncementAction();
    //     $availableCountries = $courierAnnouncementJson['available_delivery_country'];
    //     dd( 'dodac funkcje to store dla wszystkich postkodów');
    //     // $postCodesPL = $this->getPostCodesFromDataBase( $courierAnnouncement->postCodesPlAnnouncement,
    //     //                                                 $this->json->plPostCodeAction() );
    //     // $postCodesUK = $this->getPostCodesFromDataBase( $courierAnnouncement->postCodesUkAnnouncement,
    //     //                                                 $this->json->ukPostCodeAction() );

    //     // if( !empty( $postCodesPL ) ) {
    //     //     $array[ $availableCountries[ 'pl' ] ] = $postCodesPL;
    //     // }

    //     // if( !empty( $postCodesUK ) ) {
    //     //     $array[ $availableCountries[ 'uk' ] ] = $postCodesUK;
    //     // }

    //     return $array;

    // }

    private function getImagesLinks( $data ) {
        if ( count( $data ) === 0 ) {
            return [];
        }

        $linksArray = [];
        foreach( $data as $link ) {
            $linksArray[] = $link->image_link;
        }

        return $linksArray;
    }

    private function generateDates( $dates ) {
        $array = [];
        $iterator = 0;
        foreach( $dates as $date ) {
            $array[ $iterator ] = $date[ 'date' ];
            $array[ $iterator ] .= ':  ';
            $array[ $iterator ] .= __( 'base.direction_print_full_name_' . $date[ 'dir_from' ] );
            $array[ $iterator ] .= ' -> ';
            $array[ $iterator ] .= __( 'base.direction_print_full_name_' . $date[ 'dir_to' ] );

            if ( $date[ 'description' ] != null ) {
                $array[ $iterator ] .= ' ( ';
                $array[ $iterator ] .= $date[ 'description' ];
                $array[ $iterator ] .= ' )';
            }
            $iterator++;
        }

        return $array;
    }

    public function edit( Request $request, string $id ) {
        //dd( 'edit', $id );
        //$fullAnnouncement = $this->getCourierAnnouncementWithRelations( $id );
        $editData = $this->generateDataForEdit( $id );
        // dd( $editData );
        $company = UserModel::with('company')->find( auth()->user()->id );
        $extensions = $this->generateAcceptedFileFormatForCreateBlade();
        $contactData = $this->generateDataForContact( $company );
        $headerData = $this->generateCourierAnnouncementCreateFormHeader();
        $directionsData = $this->json->directionsAction();
        $cargoData = $this->json->cargoAction();
        session()->put('_old_input', $editData );
        $request->merge( $this->generateDataForEditRequest( $editData ) );
        return view( 'courier_announcement_create_form' )
            ->with( 'extensions', $extensions )
            ->with( 'contactData', $contactData )
            ->with( 'headerData', $headerData )
            ->with( 'directionsData', $directionsData )
            ->with( 'editMode', true )
            ->with( 'announcementNumber', $id )
            ->with( 'cargoData', $cargoData);
    }

    public function updateEdit( Request $request ) {
        $fullAnnouncement = $this->getCourierAnnouncementWithRelations( $request->input( 'announcement_number' ) );
        $fullAnnouncement->cargoTypeAnnouncement()->delete();
        $fullAnnouncement->imageAnnouncement()->delete();
        $fullAnnouncement->dateAnnouncement()->delete();
        $fullAnnouncement->postCodesPlAnnouncement()->delete();
        $fullAnnouncement->postCodesUkAnnouncement()->delete();
        $fullAnnouncement->contactAnnouncement()->delete();
        $fullAnnouncement->additionalPostCodes()->delete();
        $fullAnnouncement->save();
        $this->storeContactData( $request, $fullAnnouncement->id );
        $this->storeImages( $request, $fullAnnouncement->id );
        $this->storeCargos( $request, $fullAnnouncement->id );
        $this->storeDates( $request, $fullAnnouncement->id );
        $this->storeAllPostCodes( $request, $fullAnnouncement->id );

        return $this->addAnnouncementEditConfirmation();
    }

    public function destroy( string $id ) {
        //dodac do archiwum
        $this->storeFullAnnouncementArchive( $id );

        $fullAnnouncement = $this->getCourierAnnouncementWithRelations( $id );
        $fullAnnouncement->delete();

        return $this->addAnnouncementDeleteConfirmation();
    }

    private function storeFullAnnouncementArchive( $id ) {
        $fullCourierAnnouncement = $this->getCourierAnnouncementWithRelations( $id );
        $courierAnnouncement = new CourierAnnouncementArchive( $fullCourierAnnouncement->getAttributes() );
        $courierAnnouncement->save();

        $this->storeContactDataArchive( $fullCourierAnnouncement->dateAnnouncement[ 0 ], $id );
        $this->storeImagesArchive( $fullCourierAnnouncement->imageAnnouncement, $id );
        $this->storeCargosArchive( $fullCourierAnnouncement->cargoTypeAnnouncement, $id );
        $this->storeDatesArchive( $fullCourierAnnouncement->dateAnnouncement, $id );
        $this->storePostCodesPLArchive( $fullCourierAnnouncement->postCodesPlAnnouncement[ 0 ], $id );
        $this->storePostCodesUKArchive( $fullCourierAnnouncement->postCodesUkAnnouncement[ 0 ], $id );
    }

    private function storeContactDataArchive( $data, $id ) {
        //dd($data[ 0 ]);
        $contactArchive = new CourierAnnouncementContactArchive ( $data->getAttributes()  );
        $contactArchive->announcementId()->associate( $id );
        $contactArchive->save();
    }

    private function storeImagesArchive( $data, $id ) {
        foreach( $data as $image ) {
            $imageArchive = new CourierAnnouncementImagesArchive ( $image->getAttributes()  );
            $imageArchive->announcementId()->associate( $id );
            $imageArchive->save();
        }
    }

    private function storeCargosArchive( $data, $id ) {
        foreach( $data as $cargo ) {
            $cargoArchive = new CargoTypesArchive ( $cargo->getAttributes()  );
            $cargoArchive->announcementId()->associate( $id );
            $cargoArchive->save();
        }
    }

    private function storeDatesArchive( $data, $id ) {
        foreach( $data as $date ) {
            $dateArchive = new CourierTravelDateArchive ( $date->getAttributes()  );
            $dateArchive->announcementId()->associate( $id );
            // dd( $dateArchive );
            $dateArchive->save();
        }
    }

    private function storePostCodesPLArchive( $data, $id ) {
        // dd($data);
        $PostCodesArchive = new PostCodePlArchive ( $data->getAttributes()  );
        $PostCodesArchive->announcementId()->associate( $id );
        $PostCodesArchive->save();
    }

    private function storePostCodesUKArchive( $data, $id ) {
        $PostCodesArchive = new PostCodeUkArchive ( $data->getAttributes()  );
        $PostCodesArchive->announcementId()->associate( $id );
        $PostCodesArchive->save();
    }

    private function generateDataForContact( $data ) {
        $contactArray = [];

        $contactArray[ 'name' ] = $data->name;
        $contactArray[ 'surname' ] = $data->surname;
        $contactArray[ 'email' ] = $data->email;
        $contactArray[ 'telephone_number' ] = $data->phone_number;
        $contactArray[ 'additional_telephone_number' ] = '';

        if ( $data->relationLoaded('company') && $data->company !== null ) {
            $contactArray[ 'company' ] = $data->company->company_name;
            $contactArray[ 'street' ] = $data->company->company_address;
            $contactArray[ 'city' ] = $data->company->company_city;
            $contactArray[ 'post_code' ] = $data->company->company_post_code;
            $contactArray[ 'country' ] = $data->company->company_country;
            $contactArray[ 'website' ] = $data->company->website;
            if ( $data->phone_number != $data->company->company_phone_number ) {
                $contactArray[ 'additional_telephone_number' ] = $data->company->company_phone_number;
            }
        }

        return $contactArray;
    }

    private function generateAcceptedFileFormatForCreateBlade() {
        $extensions = $this->json->courierAnnouncementAction()['accept_format_picture_file'];
        $result = '';
        for ( $i = 0; $i < count( $extensions ); $i++ ) {
            if ( $i === count( $extensions ) - 1 ) {
                $result .= '.' . $extensions[ $i ];
            } else {
                $result .= '.' . $extensions[ $i ] . ', ';
            }
        }
        return $result;
    }

    private function generateAcceptedFileFormatForVerification() {
        $extensions = $this->json->courierAnnouncementAction()['accept_format_picture_file'];
        $result = '';
        for ( $i = 0; $i < count( $extensions ); $i++ ) {
            if ( $i === 0 ) {
                $result .= $extensions[ $i ];
            } else {
                $result .= ',' . $extensions[ $i ];
            }
        }
        return $result;
    }

    private function saveImagesFilesInTempFolder( $files, $savePatch ) {
        if ( $files ) {
            foreach( $files as $file ) {
                Storage::put( $savePatch, $file );
            }
        }
    }

    private function generateImagesFilesPath( $separator = DIRECTORY_SEPARATOR, $isPublicFolder = true ) {
        $user = Auth::user();
        $userNameFolder = $user->username  . "_" . $user->id;
        $public = $isPublicFolder ? $this->publicFolder . $separator : '';
        return (
            $public .
            $this->personalMainFolderImages .
            $separator .
            $userNameFolder .
            $separator .
            $this->courierAnnouncementCategoryFolder
        );
    }

    private function generateImagesTempFilesPath( $separator = DIRECTORY_SEPARATOR ) {
        $user = Auth::user();
        $personalImagesUserFolder = $user->username  . "_" . $user->id . '_temp_images';
        return (
            $this->publicFolder .
            $separator .
            $this->tempUserFolder .
            $separator .
            $personalImagesUserFolder
        );
    }

    private function validateAllRequestData( $request ) {
        // dd($request->all());
        $loginUser = Auth::user();
        $rules = $this->generateAllCargoRules( $request );
        $messages = [
            'all_pictures_number.lte' => __( 'base.courier_announcement_image_number_error_message' ) . __CHECK_ACCESS_FOR_ELEMENTS( 'picture_file_input_limit', $loginUser->account_type, 'courier_announcement' ),
        ];
        $request->validate( $rules, $messages );
    }

    private function generateAllCargoRules( $request ) {
        $rules = [];
        $loginUser = Auth::user();

        $nameRules[ "courier_announcement_name" ] = 'required';
        $nameRules[ "all_pictures_number" ] = 'lte:' . __CHECK_ACCESS_FOR_ELEMENTS( 'picture_file_input_limit', $loginUser->account_type, 'courier_announcement' );
        $cargoRules = $this->generateCargoValidationRules( $request );
        $dateRules = $this->generateDateValidationRules( $request );
        $postCodesRules = $this->generateAllPostCodesValidationRules( $request );
        $experienceDate = $this->generateExperienceDateRules( $request );
        $imagesRules = $this->generateImagesValidationRules( $request->file('files') );
        $contactRules = $this->generateContactValidationRules( $request );

        $rules = array_merge( $nameRules,
                              $cargoRules,
                              $dateRules,
                              $postCodesRules,
                              $experienceDate,
                              $contactRules,
                              $imagesRules );

        return $rules;
    }

    private function generateContactValidationRules( $request ) {
        $rules = [];
        $rules[ 'contact_detail_name' ] = 'required|max:100';
        $rules[ 'contact_detail_surname' ] = 'required|max:100';
        $rules[ 'contact_detail_email' ] = 'required|email|max:100';
        $rules[ 'contact_detail_telephone_number' ] = 'required|numeric|max_digits:15';
        return $rules;
    }

    private function generateCargoValidationRules( $request ) {
        $rules = [];

        $rules[ "cargo_name_1" ] = 'required|max:200';
        $rules[ "cargo_description_1" ] = 'required|max:1200';
        $rules[ "cargo_price_1" ] = 'required|numeric|min:1|max:99999';
        $rules[ "select_currency_1" ] = 'required|not_in:option_default';

        for( $i = 2; $i < 10000; $i++ ) {
            $cargoName = $request->input(  'cargo_name_' . $i );
            $cargoDescription = $request->input(  'cargo_description_' . $i );
            $cargoPrice = $request->input(  'cargo_price_' . $i );
            $selectCurrency = $request->input(  'select_currency_' . $i );

            if( $cargoName === null &&
                $cargoDescription === null &&
                ( $cargoPrice === "0" || $cargoPrice === null ) &&
                ( $selectCurrency == "option_default" || $selectCurrency == null ) ) {
                break;
            } else {
                $rules[ "cargo_name_" . $i ] = 'required|max:100';
                $rules[ "cargo_description_" . $i ] = 'required|max:1200';
                $rules[ "cargo_price_" . $i ] = 'required|numeric|min:1|max:99999';
                $rules[ "select_currency_" . $i ] = 'required|not_in:option_default';
            }
        }
        return $rules;
    }

    private function generateDateValidationRules( $request ) {
        $rules = [];

        $rules[ "from_date_directions_select_1" ] = 'required|not_in:default_direction';
        $rules[ "to_date_directions_select_1" ] = 'required|not_in:default_direction';
        $rules[ "date_input_1" ] = 'required|date|after:today';

        for( $i = 2; $i < 10000; $i++ ) {
            $dateDirectionFrom = $request->input(  'from_date_directions_select_' . $i );
            $dateDirectionTo = $request->input(  'to_date_directions_select_' . $i );
            $date = $request->input(  'date_input_' . $i );
            if( ( $dateDirectionFrom === null || $dateDirectionFrom === "default_direction" ) &&
                ( $dateDirectionTo === null || $dateDirectionTo === "default_direction" ) &&
                  $date === null ) {
                break;
            } else {
                $rules[ "from_date_directions_select_" . $i ] = 'required|not_in:default_direction';
                $rules[ "to_date_directions_select_" . $i ] = 'required|not_in:default_direction';
                $rules[ "date_input_" . $i ] = 'required|date|after:today';
            }
        }
        return $rules;
    }

    // private function generatePostCodePLValidationRules( $request, $json ) {
    //     dd( $request->all() );
    //     $rules = [];
    //     $postCodePLRequired = true;
    //     foreach( $json as $postCode ) {
    //         $postCodeName =  $postCode;
    //         if( $request->input( $postCodeName ) !== "0" && $request->input( $postCodeName ) !== null && $request->input( $postCodeName ) !== "" ) {
    //             $postCodePLRequired = false;

    //             break;
    //         } else {
    //             $rules[ $postCodeName ] = 'required|not_in:0';
    //         }
    //     }

    //     if( $postCodePLRequired === true ) {
    //         return $rules;
    //     }

    //     return [];
    // }

    private function getDirectionsFromRequest( $request ) {
        $loginUser = Auth::user();
        $dateElementNumber = __CHECK_ACCESS_FOR_ELEMENTS( 'number_of_type_date', $loginUser->account_type, 'courier_announcement' );
        $directionArray = [];
        for( $i = 1; $i <= $dateElementNumber; $i++ ) {
            $fromDirection = $request->input( 'from_date_directions_select_' . $i );
            $toDirection = $request->input( 'to_date_directions_select_' . $i );

            if( ( $fromDirection == 'default_direction' || $fromDirection == null || $fromDirection == '0' ) &&
                ( $toDirection == 'default_direction' || $toDirection == null || $toDirection == '0' ) ) {
                break;
            }

            if ( !isset( $directionArray[ $fromDirection ] ) ) {
                $directionArray[ $fromDirection ] = true;
            }
            if ( !isset( $directionArray[ $toDirection ] ) ) {
                $directionArray[ $toDirection ] = true;
            }
        }
        return $directionArray;
    }

    private function generateAllPostCodesValidationRules( $request ) {
        $rules = [];
        foreach( $this->getDirectionsFromRequest( $request ) as $key => $direction ) {

            $fromDirectionPostcodesRules =  $this->generateSinglePostCodesValidationRules( $request, $key );
            $rules = array_merge( $rules, $fromDirectionPostcodesRules);
        }
        return $rules;


    }

    private function generateSinglePostCodesValidationRules( $request, $direction ) {
        $rules = [];
        $postCodeRequired = true;
        foreach( $this->json->getPostCodes( $direction  ) as $postCode ) {
            if( $request->input( $postCode ) !== "0" && $request->input( $postCode ) !== null && $request->input( $postCode ) !== "" ) {
                $postCodeRequired = false;
                break;
            } else {
                $rules[ $postCode ] = 'required|not_in:0';
            }
        }

        if( $postCodeRequired === true ) {
            return $rules;
        }
        return [];
    }

    private function generateExperienceDateRules( $request ) {
        $rules = [];
        if ( $request->input( 'experience_for_premium_date' ) !== 1 && $request->input( 'experience_for_premium_date' ) !== '1'  ) {
            $rules[ 'experience_announcement_date_input' ] = 'required|date|after:today';
        }
        return $rules;
    }

    private function generateImagesValidationRules( $files ) {
        $loginUser = Auth::user();
        $rules = [];
        $extensions = $this->generateAcceptedFileFormatForVerification();
        $maxFileSize = __CHECK_ACCESS_FOR_ELEMENTS( 'max_size_single_image_file', $loginUser->account_type, 'courier_announcement' );
        $rules[ 'images.*' ] = 'image|mimes:' . $extensions . '|max:' . $maxFileSize;
        return $rules;
    }

    private function generateSummaryAnnouncementTitle( $request, $company ) {
        $courierAnnouncement = $this->json->courierAnnouncementAction();
        $maxCargoInTitle = $courierAnnouncement[ 'max_cargo_names_in_title' ];
        $cargoNumber = $request->input('cargo_number_visible');
        $titleFront = __( 'base.courier_announcement_full_title_summary_front' );
        $titleMid = __( 'base.courier_announcement_full_title_summary_mid' );
        $titleEnd = $cargoNumber > $maxCargoInTitle ? __( 'base.courier_announcement_full_title_summary_end' ) : "";
        $cargoNames = "";

        if ( $company->company !== null ) {
            $companyName = $company->company->company_name;
        } else {
            $companyName = '';
        }

        for( $i = 1; $i <= min( $cargoNumber, $maxCargoInTitle ); $i++ ) {
            if ( $i > 1 ) {
                $cargoNames .= ", ";
            } else {
                $cargoNames .= " ";
            }
            $cargoNames .= $request->input('cargo_name_' . $i );
        }
        return ( $titleFront . $companyName . $titleMid . $cargoNames . $titleEnd );
    }

    private function generateAnnouncementTitlesInList($announcements) {
        $allTitles = array();
        $courierAnnouncement = $this->json->getJsonData('courier_announcement');
        $maxCargoInTitle = $courierAnnouncement['max_cargo_names_in_title'];
        $titleFront = __('base.courier_announcement_full_title_summary_front');
        $titleMid = __('base.courier_announcement_full_title_summary_mid');

        foreach ($announcements as $announcement) {
            $cargoNumber = count($announcement->cargoTypeAnnouncement);
            $titleEnd = $cargoNumber > $maxCargoInTitle ? __('base.courier_announcement_full_title_summary_end') : "";

            $cargoNames = "";
            $companyName = UserModel::with('company')->find($announcement->author)->company->company_name;

            for ($i = 0; $i < min($cargoNumber, $maxCargoInTitle); $i++) {
                if ($i > 1) {
                    $cargoNames .= ", ";
                } else {
                    $cargoNames .= " ";
                }
                $cargoNames .= $announcement->cargoTypeAnnouncement[ $i ]->cargo_name;
            }

            $allTitles[] = ( $titleFront . $companyName . $titleMid . $cargoNames . $titleEnd );
        }
        return $allTitles;
    }

    private function generateDataForDeliveryCountryToSession() {
        $fullCountryArray = [];
        $courierAnnouncement = $this->json->courierAnnouncementAction();
        $availableCountries = $courierAnnouncement['available_delivery_country'];

        foreach( $availableCountries as $key => $value ) {
            $singleCountry = [];
            $singleCountry[ 'country_name' ] = $key;
            $singleCountry[ 'translate_text' ] = $value;
            $fullCountryArray[ $key ] = $singleCountry;
        }

        return $fullCountryArray;
    }

    private function generateLinksForImages( $files, $tempPath, $iteratorBegin ) {
        $pathsArray = [];
        $iterator = $iteratorBegin + 1;
        if( $files !== null && count( $files ) > 0 ) {
            foreach( $files as $file ) {
                $pathsArray[ 'image' . $iterator ] = Storage::url( $tempPath . '/' .$file->hashName() );
                $iterator++;
            }
        }
        return $pathsArray;
    }

    private function generatePrevLinksArrayForEdit( $request ) {
        $linksArray = [];
        for( $i = 1; ; $i++ ) {
            //$isForDelete = $request->input( 'old_image_info_' . $i );
            $value = $request->input( 'old_image_' . $i );
            if ( $value == null ) {
                break;
            }
            //if( $isForDelete == 'noDelete' ) {
            $linksArray[ 'image'. $i ] = $value;
            //}
        }

        return $linksArray;
    }

    private function generatePrevLinksArrayForSummary( $request ) {
        $linksArray = [];
        for( $i = 1; ; $i++ ) {
            $isForDelete = $request->input( 'old_image_info_' . $i );
            $value = $request->input( 'old_image_' . $i );
            if ( $value == null ) {
                break;
            }
            if( $isForDelete == 'noDelete' ) {
                $linksArray[ 'summary_image'. $i ] = $value;
            }
        }

        return $linksArray;
    }

    // private function getFilesNumber( $request ) {
    //     return is_null($request->file('files')) ? 0 : count($request->file('files'));
    // }

    // private function generateCourierAnnouncementSummaryHeader() {
    //     $directions = $this->json->directionsAction();
    //     $allPostCodes = $this->generateDirectionsPostcodesArray();

    //     // $postCodesPL = $this->json->getPostCodesDataByCountry('pl');
    //     // $postCodesUK = $this->json->getPostCodesDataByCountry('uk');

    //     return compact(
    //         'directions',
    //         'postCodesPL',
    //         'postCodesUK'
    //     );
    // }

    private function generateAllPostCodesSummary( $request ) {
        $userDirections = $this->getDirectionsFromRequest( $request );
        $allPostCodes = [];
        foreach( $userDirections as $key => $direction ) {
            $oneDirectionPostCodes = [];
            foreach( $this->json->getPostCodes( $key ) as $postCode ) {
                if( $request->input( $postCode ) != 0 ) {
                    $oneDirectionPostCodes[] = $postCode;
                }
            }
            $allPostCodes[ $key ] = $oneDirectionPostCodes;
        }
        return $allPostCodes;
    }

    private function generateDirectionsPostcodesArray() {
        $directionsArray = [];
        foreach( $this->json->directionsAction() as $direction ) {
            $directionsArray[ $direction[ 'name' ] ] = $this->json->getPostCodes( $direction[ 'name' ] );
        }
        return $directionsArray;
    }

    private function generateCourierAnnouncementCreateFormHeader() {
        $loginUser = Auth::user();
        $cargoElementNumber = __CHECK_ACCESS_FOR_ELEMENTS( 'number_of_type_cargo', $loginUser->account_type, 'courier_announcement' );
        $dateElementNumber = __CHECK_ACCESS_FOR_ELEMENTS( 'number_of_type_date', $loginUser->account_type, 'courier_announcement' );
        $picturesNumber = __CHECK_ACCESS_FOR_ELEMENTS( 'picture_file_input_limit', $loginUser->account_type, 'courier_announcement' );

        $allPostCodes = $this->generateDirectionsPostcodesArray();
        $directions = $this->json->directionsAction();
        $permDate = $this->json->courierAnnouncementAccessElementsAction()['perm_experience_date_for_premium'];
        $pictureFileFormat = $this->json->courierAnnouncementAction()['accept_format_picture_file'];

        return compact(
            'cargoElementNumber',
            'dateElementNumber',
            'picturesNumber',
            'permDate',
            'pictureFileFormat',
            'loginUser',
            'allPostCodes',
            'directions'
        );
    }

    private function generateTempFolderIfDontExist() {
        $user = Auth::user();
        $personalImagesUserFolder = $user->username  . "_" . $user->id . '_temp_images';
        $tempPatch = storage_path(
            'app' .
            DIRECTORY_SEPARATOR .
            $this->publicFolder .
            DIRECTORY_SEPARATOR .
            $this->tempUserFolder .
            DIRECTORY_SEPARATOR .
            $personalImagesUserFolder
        );

        if ( !File::isDirectory( $tempPatch ) ) {
            File::makeDirectory( $tempPatch, 0755, true );
        }
    }

    private function generateImagesFolderIfDontExist() {
        $user = Auth::user();
        $userNameFolder = $user->username  . "_" . $user->id;
        $tempPatch = storage_path(
            'app' .
            DIRECTORY_SEPARATOR .
            $this->publicFolder .
            DIRECTORY_SEPARATOR .
            $this->personalMainFolderImages .
            DIRECTORY_SEPARATOR .
            $userNameFolder .
            DIRECTORY_SEPARATOR .
            $this->courierAnnouncementCategoryFolder
        );

        if ( !File::isDirectory( $tempPatch ) ) {
            File::makeDirectory( $tempPatch, 0755, true );
        }
    }

    private function generateContactData( $request ) {
        $contactJson = $this->json->courierAnnouncementAction()[ 'contact_form_fields' ];
        $contactArray = [];

        foreach( $contactJson[ 'personal' ] as $field ) {
            $contactArray[ $field ] = $request->input( 'contact_detail_' . $field, null );
        }

        foreach( $contactJson[ 'company' ] as $field ) {
            $contactArray[ $field ] = $request->input( 'contact_detail_' . $field, null );
        }

        return $contactArray;
    }

    private function generatePostCodesFromDataBase( $announcement ) {
        $directions = [];
        foreach ( $this->json->directionsAction() as $dir ) {
            $directions[ $dir[ 'name' ] ] = [];
            foreach( $announcement[ 'postCodes' . $dir[ 'request_name' ] . 'Announcement' ][ 0 ]->getAttributes() as $key => $value ) {
                if ( $value != null && $key != 'id' && $key != 'courier_announcement_id' && $key != 'created_at' && $key != 'updated_at' ) {
                    $directions[ $dir[ 'name' ] ][ $key ] = $value;
                }
            }
        }
        return $directions;
    }

    private $json = null;
    private $personalMainFolderImages = 'personal_images';
    private $courierAnnouncementCategoryFolder = 'courier_announcement';
    private $publicFolder = 'public';
    private $tempUserFolder = 'temporary_user_images';
}
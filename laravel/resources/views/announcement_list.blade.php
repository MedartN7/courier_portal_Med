@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('base.announcement_look_for_transport') }}</div>
                <div class="card-body">

                    @foreach ( $announcements as $announcement )
                        <table class="table table-sm table-light">
                            <thead>
                              <tr class="table-active">
                                <th colspan="2" scope="col">{{ $announcement->title }}</th>

                              </tr>
                            </thead>
                            <tbody>
                              <tr>
                                <th scope="row"></th>
                                <td>
                                    <strong>{{ __( 'base.delivery_from' ) }}</strong>
                                    {{' ' . __( 'base.direction_print_full_name_genitive_' . $announcement[ 'direction_sending' ] ) . ' ' .
                                    $announcement[ 'post_code_prefix_sending' ] . $announcement[ 'post_code_postfix_sending' ] . ' ' . $announcement[ 'city_sending' ] . ' | ' }}

                                    <strong>{{ __( 'base.delivery_to' ) }}</strong>
                                    {{' ' . __( 'base.direction_print_full_name_genitive_' . $announcement[ 'direction_receiving' ] ) . ' ' .
                                    $announcement[ 'post_code_prefix_receiving' ] . $announcement[ 'post_code_postfix_receiving' ] . ' ' . $announcement[ 'city_receiving' ] }}<br>

                                </td>
                              </tr >
                              <tr>
                                <th scope="row">&nbsp&nbsp</th>

                                <td class="d-flex align-items-center">
                                    <div class="text-start">
                                        <form class="d-inline-block me-2" action="{{ route( 'user_announcement.show', ['user_announcement' => $announcement->id ] ) }}" method="GET" id="user_announcement_single_announcement_show">
                                            <button type="submit" class="btn btn-primary">{{ __( 'base.details_announcement_button' ) }}</button>
                                        </form>
                                        @if ( Auth::user()->id == $announcement->author )
                                            <form class="d-inline-block me-2" action="{{ route('user_announcement.edit', ['user_announcement' => $announcement->id ] ) }}" method="GET" id="user_announcement_single_announcement_edit">
                                                <button type="submit" class="btn btn-success">{{ __( 'base.edit_announcement_button' ) }}</button>
                                            </form>
                                            <form class="d-inline-block me-2" action="{{ route('user_announcement.destroy', ['user_announcement' => $announcement->id ] ) }}" method="GET" id="user_announcement_single_announcement_delete">
                                                <button type="submit" class="btn btn-danger">{{ __( 'base.delete_announcement_button' ) }}</button>
                                            </form>
                                            {{-- @if ( $announcement->priority === null )
                                                <form class="d-inline-block">
                                                    <button type="submit" class="btn btn-warning">{{ __( 'base.upgrade_announcement_button' ) }}</button>
                                                </form>
                                            @endif --}}

                                        @endif
                                    </div>
                                    <div class="small text-end ms-auto">{{ __( 'base.date_look_for_announcement' ) . $announcement->created_at . " | " . __( 'base.date_look_for_announcement_experience' ) . $announcement->experience_date }}</div>
                                </td>

                              </tr>
                            </tbody>
                          </table>
                    @endforeach
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

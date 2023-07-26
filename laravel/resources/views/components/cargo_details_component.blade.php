
<div>
    <div class="row mb-3">
        <table class="table table-hover table-sm table-secondary">
            <thead>
                <tr class="table-active">
                    <th scope="col" colspan="12" class="text-center font-weight-bold">{{__( 'base.' . $name . '_base' )}}</th>
                </tr>
              <tr>
                <th></th>

                @foreach ( $params as $param )
                    <th scope="{{ $param[ 'id' ] }}" class="text-center">{{__( 'base.' . $param[ 'id' ] . '_base' )}}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
                    @for ( $i = 0; $i < $number; $i++ )
                        <tr>
                            @if ( $name === "human" )
                                @php $i= $number @endphp
                            @endif
                        <th scope="{{ $name }}row" class="text-center align-middle">{{ $i + 1 }}</th>
                        @foreach ( $params as $param )
                            <td>
                                <div class="d-flex align-items-center">
                                @if ( $param[ 'type' ] === "number" )
                                    <input name="{{ $name }}" type="number" id="{{ $name }}" class="form-control" value="0"/>
                                @elseif ( $param[ 'type' ] === "text" )
                                    <input name="{{ $name }}" type="text" id="{{ $name }}" class="form-control"/>
                                @elseif ( $param[ 'type' ] === "textarea" )
                                    <textarea id="{{ $name }}" class="form-control" name="{{ $name }}" rows="{{ $param[ 'textarea_size' ] }}"></textarea>
                                @endif
                                <label class="form-label mt-1" for="{{ $name }}">&nbsp{{__( 'base.' . $param[ 'id' ] )}}</label>
                                </div>
                            </td>
                        @endforeach
                        </tr>
                    @endfor

            </tbody>
          </table>
    </div>
</div>

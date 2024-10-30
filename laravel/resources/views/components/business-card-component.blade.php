<div class="card m-2" style="width: 300px; border-radius: 8px;">
    <div class="card-header"><strong>{{ $element['name'] }}</strong></div>
    <div class="card-body d-flex align-items-start p-1">
        <!-- Lewy div z logotypem -->
        <div class="d-flex align-items-right ps-1 justify-content-center" style="width: 75px;">
            <img src="{{ asset("images/businessCardImages/" . $element['pictureName']) }}" alt="Logo" class="img-fluid" style="max-width: 100%; height: auto; max-height: 75px;">
        </div>

        <!-- Prawy div z trzema elementami, jeden pod drugim -->
        <div class="d-flex flex-column justify-content-between flex-grow-1 ps-2 pe-1 small ">
            <div class="border-bottom pb-1 small" style="height: 24px;"><i class="bi bi-telephone"></i> {{ $element['tel'] }}</div>
            <div class="border-bottom pb-1" style="height: 24px;"><i class="bi bi-envelope"></i> {{ __( $element['email'] ) }}</div>
            <div class="border-bottom pb-1" style="height: 24px;"><i class="bi bi-globe-americas"></i> {{ __( $element['web'] ) }}</div>
            
        </div>
    </div>
    <div style="font-size: 0.70rem;" class="pe-1 ps-1 m-1">{{ __( $element['description'] ) }}</div>
</div>





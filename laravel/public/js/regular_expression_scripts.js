// add to blade in input:
// data-message="{{ $message }}" pattern="{{ $pattern }}"

function regularExpressionsControl( element ) { 
    let regex = new RegExp( element.pattern );
    let message = element.message;

    if( checkFieldValueIfMatchWithRegex( element.value, regex ) ) {
        changeFieldIfIsOk( element );
    } else {
        changeFieldIfIsWrong( element, message );
    }
        
}

function checkFieldValueIfMatchWithRegex( value, regex ) {
    const isMatch = regex.test( value )
    console.log( value, regex, isMatch )
    return isMatch;
}

function changeFieldIfIsWrong( field, message ) {
    if ( !field.parentNode.querySelector('.error_regex_message') ) {
        field.style.backgroundColor = '#ffcccc'
        field.style.border = '2px solid red';

        const errorMessage = document.createElement('div');
        errorMessage.textContent = field.dataset.message;
        errorMessage.style.color = 'red';
        errorMessage.style.fontSize = '12px'; 
        errorMessage.style.marginTop = '5px'; 
        errorMessage.classList.add( 'error_regex_message' );
        field.parentNode.insertBefore( errorMessage, field.nextSibling );
    }
}

function changeFieldIfIsOk( field ) {
    field.style.backgroundColor = ''
    field.style.border = '';

    const errorMessage = field.parentNode.querySelector('.error_regex_message');
    
    if ( errorMessage ) {
        errorMessage.remove();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll( '.form_input').forEach( function( element ) {
        element.addEventListener( 'input', function( event ) {
            regularExpressionsControl( element );
        });
    });
});
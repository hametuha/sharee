/**
 * Description
 */

/*global ShareeBilling: false*/

( ( $ ) => {

  'use strict';

  $( document ).ready( () => {
    $( '#doaction,#doaction2' ).click( function( e ) {
      e.preventDefault();
      const action = $( this ).prev( 'select' ).val();
      let ids = []; // Grab user ids.
      $( '.billing-user:checked' ).each( function( index, input ) {
        ids.push( $( input ).val() );
      });
      if ( ! ids.length ) {
        return false;
      }
      switch ( action ) {
        case 'update':
          $.post( ShareeBilling.endpoint, {
            'action': 'user_billing',
            '_wpnonce': ShareeBilling.nonce,
            'year': $( 'select[name="year"]' ).val(),
            'month': $( 'select[name="monthnum"]' ).val(),
            'user_ids': ids
          }).done( ( result ) => {
            alert( result.message );
            if ( result.success ) {
              window.location.reload();
            }
          }).fail( ( response ) => {
            let message = ShareeBilling.defaultError;
            if ( response.responseJSON && response.responseJSON.data ) {
              message = response.responseJSON.data.message;
            }
            if ( window.console ) {
              console.log( response );
            }
            alert( message );
          });
          break;
        case 'download':
          const dateObj = new Date();
          const date = window.prompt( ShareeBilling.transferDate,  [ dateObj.getMonth() + 1,  dateObj.getDate() ].map( function( num ) {
            return ( '0' + num ).slice( -2 );
          }).join( '' ) );
          if ( ! date ) {
            return;
          }
          const $form = $( 'form[target="sharee-csv-downloader"]' );
          $form.find( 'input[name="year"]' ).val( $( 'select[name="year"]' ).val() );
          $form.find( 'input[name="month"]' ).val( $( 'select[name="monthnum"]' ).val() );
          $form.find( 'input[name="date"]' ).val( date );
          $form.find( 'p' ).empty();
          ids.forEach( ( id, index )=>{
            $form.find( 'p' ).append( `<input type="checkbox" name="user_ids[]" value="${id}" checked />` );
          });
          $form.submit();
          break;
        default:
          return;
      }
    });
  });

})( jQuery );

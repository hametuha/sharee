/**
 * Description
 */

/*global BankHelper: false*/

jQuery( document ).ready( function( $ ) {

  'use strict';

  const toggleSelect2 = function() {
     const bankCode = $( '#_bank_group_code' ).val();
     $( '#_bank_branch' ).prop( 'disabled', ! /\d+/.test( bankCode ) );
  };


  // Select Bank code.
  $( '#_bank_group' ).select2({
    ajax: {
      delay: 500,
      url: 'https://bankcode-api.appspot.com/api/bank/JP',
      dataType: 'jsonp',
      data: function( params ) {
        const query = {
          name: params.term
        };
        if ( BankHelper.apiKey ) {
          query.apiKey = BankHelper.apiKey;
        }
        return query;
      },
      processResults: function( data ) {
        if ( ! data.data.length ) {
          return { results: [] };
        }
        return {
          results: data.data.map( function( item ) {
            return {
              id: item.name,
              text: item.name,
              code: item.code
            };
          })
        };
      }
    }
  }).on( 'select2:select', function( event ) {
    $( '#_bank_group_code' ).val( event.params.data.code ).trigger( 'change' );
  });

  // Select Branch code.
  $( '#_bank_branch' ).select2({
    ajax: {
      delay: 500,
      url: 'https://bankcode-api.appspot.com/api/bank/JP/0000',
      dataType: 'jsonp',
      beforeSend: function( xhr ) {
        let urls = this.url.split( '?' );
        urls[0] = urls[0].replace( /\/\d+$/, '/' + $( '#_bank_group_code' ).val() );
        this.url = urls.join( '?' );
      },
      data: function( params ) {
        const query = {
          name: params.term
        };
        if ( BankHelper.apiKey ) {
          query.apiKey = BankHelper.apiKey;
        }
        return query;
      },
      processResults: function( data ) {
        if ( ! data.data.length ) {
          return { results: [] };
        }
        return {
          results: data.data.map( function( item ) {
            return {
              id: item.name,
              text: item.name,
              code: item.code
            };
          })
        };
      }
    }
  }).on( 'select2:select', function( event ) {
    $( '#_bank_branch_code' ).val( event.params.data.code );
  });

  toggleSelect2();
  $( '#_bank_group_code' ).change( toggleSelect2 );

  if ( ! BankHelper.yolpKey ) {
    $( '#sharee-zip-search' ).remove();
  }
  $( '#sharee-zip-search' ).click( function( e ) {
    e.preventDefault();
    $.ajax({
      type: 'GET',
      url: 'https://map.yahooapis.jp/search/zip/V1/zipCodeSearch',
      dataType: 'jsonp',
      data: {
        appid: BankHelper.yolpKey,
        query: $( '#_billing_zip' ).val(),
        output: 'json'
      }
    }).done( function( response ) {
      if ( ! response.Feature || ! response.Feature.length ) {
        alert( BankHelper.yolpError );
        return;
      }
      $( '#_billing_address' ).val( response.Feature[0].Property.Address );
    }).fail( function( response ) {
      alert( BankHelper.yolpError );
    });
  });
});

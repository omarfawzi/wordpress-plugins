/**
 * Admin script for WooCommerce delivery time slot.
 */
jQuery( function ( $ )
{
	'use strict';

	var $container = $( '.time-slots-container' );

	// Add time slot
	$container.on( 'click', '.wdts-add-slot', function ( e )
	{
		e.preventDefault();
		var $last = $container.find( '.wdts-slot:last' ),
			$clone = $last.clone();
		$clone.find( '.wdts-add-slot' ).remove().end()
			.find( '.wdts-remove-slot' ).removeClass( 'hidden' ).end()
			.find( 'input' ).val( '' ).end()
			.insertAfter( $last );
	} );

	// Remove time slot
	$container.on( 'click', '.wdts-remove-slot', function ( e )
	{
		e.preventDefault();
		$( this ).parent().remove();
	} );
} );

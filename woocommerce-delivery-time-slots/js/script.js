/* global WDTS */

jQuery( function ( $ ) {
	'use strict';
	var $field = $( '#delivery_date' );
	var form = $('[name="checkout"]');
	var selected =  null;

	delete_cookie('date_selected');

    form.on('click', '#place_order', function() {
        selected = $('#delivery_date').val();
        jQuery.post( WDTS.ajaxUrl, {
            action: 'wdts_check_number_of_shipments',
            date: selected,
            nonce: WDTS.nonceCheckShipping
        }, function ( r ) {
            if ( r.success ) {
            	form.submit();
                return;
            }
            // alert( r.data.message );
            if ( r.data.type === 'exceed' ) {
                // $field.val( '' );
            }
        } );
    });

	function setupLocale() {
		$.datepicker.setDefaults( $.datepicker.regional[""] );
		if ( $.datepicker.regional.hasOwnProperty( WDTS.locale ) ) {
			$.datepicker.setDefaults( $.datepicker.regional[WDTS.locale] );
		}
		else if ( $.datepicker.regional.hasOwnProperty( WDTS.localeShort ) ) {
			$.datepicker.setDefaults( $.datepicker.regional[WDTS.localeShort] );
		}
	}

	/**
	 * Initialize
	 */
	function init() {
		$field.prop( 'readonly', true ).addClass( 'delivery-time' );
		if ( WDTS.isAdminPage == "1" ) {
			$( '.delivery-time' ).val( getCookie( 'date_selected' ) );
		}
		$field.datepicker( $.extend(
			WDTS.customDate,
			{
				dateFormat: WDTS.dateFormat,
				beforeShowDay: checkDate,
				maxDate: WDTS.maxDate,
				minDate: new Date( WDTS.minDate ),
				onSelect: function ( dateText ) {
					selected = dateText;
                    $('#place_order').attr('type','button');
				}
			}
		) );
	}

	/**
	 * Toggle field by shipping method
	 */
	function toggleByShippingMethod() {
		var $wrapper = $( '.woocommerce-checkout-review-order' );
		$wrapper.on( 'change', '.shipping_method', function () {
			var $methods = $wrapper.find( '.shipping_method' ),
				selected;
			if ( $methods.is( 'select' ) ) {
				selected = $methods.val();
			}
			else {
				selected = $methods.filter( ':checked' ).val();
			}
			if ( - 1 !== $.inArray( selected, WDTS.disabledShippingMethods ) ) {
				setCookie( 'date_selected', '' );
				$field.val( '' ).parent().hide();
			}
			else {
				$field.parent().show();
			}
		} );
		$wrapper.find( 'select.shipping_method' ).trigger( 'change' );
		$wrapper.find( '.shipping_method:checked' ).trigger( 'change' );
	}

	/**
	 * Check if we need to disable a date
	 *
	 * @param  date
	 *
	 * @return array
	 */

	function format_date(date,delimeter) {
        var ret = date;
        var dd = ret.getDate();
        var mm = ret.getMonth()+1;
        var yyyy = ret.getFullYear();
        if(dd<10)
        {
            dd='0'+dd;
        }

        if(mm<10)
        {
            mm='0'+mm;
        }
        return dd + delimeter + mm + delimeter + yyyy;
    }
	function checkDate( date ) {
		// Restricted shipping dates
        if ( $.inArray( format_date(date,'-'), WDTS.restrictedShippingDates ) > - 1 ) {
            return [false];
        }
		// Restricted week days
		if ( $.inArray( date.getDay(), WDTS.restrictedWeekDays ) > - 1 ) {
			return [false];
		}
		// Restricted dates
		var check, range, from, to,
			k = WDTS.restrictedDates.length;
		while ( k -- ) {
			check = WDTS.restrictedDates[k];
			// console.log(check);
			// Single date
			if ( check.indexOf( '-' ) == - 1 ) {
				if ( date.getTime() == convertStars( $.trim( check ), date ).getTime() ) {
					return [false];
				}
				continue;
			}

			// Date range
			range = check.split( '-' ),
				from = convertStars( $.trim( range[0] ), date ),
				to = convertStars( $.trim( range[1] ), date );

			if ( from <= to ) {
				if ( from <= date && date <= to ) {
					return [false];
				}
			}

			// Compare only if at least one of from date and to date contains stars ( * )
			else if ( range[0].indexOf( '*' ) != - 1 || range[1].indexOf( '*' ) != - 1 ) {
				if ( from <= date || date <= to ) {
					return [false];
				}
			}
		}

		// Date is not restricted
		return [true];
	}

	/**
	 * Convert a date string contains stars ( * ) into a Date object
	 *
	 * @param s        Date string, in format mm/dd/yyyy
	 * @param baseDate Date where we get day, month, year
	 * @return Date
	 */
	function convertStars( s, baseDate ) {
		var parts = s.split( '/' );
		[parts[0],parts[1]] = [parts[1],parts[0]];
		if ( '*' == parts[0] ) {
			parts[0] = baseDate.getMonth();
		} else {
			parts[0] = parseInt( parts[0] ) - 1;
		} // Month in Javascript Date object is counted from 0 to 11

		if ( '*' == parts[1] ) {
			parts[1] = baseDate.getDate();
		}
		if ( '*' == parts[2] ) {
			parts[2] = baseDate.getFullYear();
		}

		return new Date( parts[2], parts[0], parts[1] );
	}

	/**
	 * Set cookie when user select a delivery date
	 *
	 * @param name  Cookie name string
	 * @param value Cookie value string
	 */
	function setCookie( name, value ) {
		document.cookie = name + "=" + value + ";";
	}

	/**
	 * Get cookie save delivery date
	 *
	 * @param name Cookie name string
	 * @return string
	 */
	function getCookie( name ) {
		name = name + "=";
		var ca = document.cookie.split( ';' );
		for ( var i = 0; i < ca.length; i ++ ) {
			var c = ca[i].trim();
			if ( c.indexOf( name ) == 0 ) {
				return c.substring( name.length, c.length );
			}
		}
		return "";
	}

    function delete_cookie( name ) {
        document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
	setupLocale();
	init();
	toggleByShippingMethod();
} );

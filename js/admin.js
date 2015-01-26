jQuery(document).ready(function($) {

	// champ "Google Map"
	if($('.mbc .type_map')[0]) {
		google.maps.event.addDomListener(window, 'load', google_places_autocomplete);
	}



	// validation
	$.extend($.validator.messages, {
		required: "Ce champ est obligatoire.",
		remote: "Veuillez corriger ce champ.",
		email: "Veuillez fournir une adresse électronique valide.",
		url: "Veuillez fournir une adresse URL valide.",
		date: "Veuillez fournir une date valide.",
		dateISO: "Veuillez fournir une date valide (ISO).",
		number: "Veuillez fournir un numéro valide.",
		digits: "Veuillez fournir seulement des chiffres.",
		creditcard: "Veuillez fournir un numéro de carte de crédit valide.",
		equalTo: "Veuillez fournir encore la même valeur.",
		accept: "Veuillez fournir une valeur avec une extension valide.",
		maxlength: $.validator.format("Veuillez fournir au plus {0} caractères."),
		minlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
		rangelength: $.validator.format("Veuillez fournir une valeur qui contient entre {0} et {1} caractères."),
		range: $.validator.format("Veuillez fournir une valeur entre {0} et {1}."),
		max: $.validator.format("Veuillez fournir une valeur inférieur ou égal à {0}."),
		min: $.validator.format("Veuillez fournir une valeur supérieur ou égal à {0}."),
		maxWords: $.validator.format("Veuillez fournir au plus {0} mots."),
		minWords: $.validator.format("Veuillez fournir au moins {0} mots."),
		rangeWords: $.validator.format("Veuillez fournir entre {0} et {1} mots."),
		letterswithbasicpunc: "Veuillez fournir seulement des lettres et des signes de ponctuation.",
		alphanumeric: "Veuillez fournir seulement des lettres, nombres, espaces et soulignages",
		lettersonly: "Veuillez fournir seulement des lettres.",
		nowhitespace: "Veuillez ne pas inscrire d'espaces blancs.",
		ziprange: "Veuillez fournir un code postal entre 902xx-xxxx et 905-xx-xxxx.",
		integer: "Veuillez fournir un nombre non décimal qui est positif ou négatif.",
		vinUS: "Veuillez fournir un numéro d'identification du véhicule (VIN).",
		dateITA: "Veuillez fournir une date valide.",
		time: "Veuillez fournir une heure valide entre 00:00 et 23:59.",
		phoneUS: "Veuillez fournir un numéro de téléphone valide.",
		phoneUK: "Veuillez fournir un numéro de téléphone valide.",
		mobileUK: "Veuillez fournir un numéro de téléphone mobile valide.",
		strippedminlength: $.validator.format("Veuillez fournir au moins {0} caractères."),
		email2: "Veuillez fournir une adresse électronique valide.",
		url2: "Veuillez fournir une adresse URL valide.",
		creditcardtypes: "Veuillez fournir un numéro de carte de crédit valide.",
		ipv4: "Veuillez fournir une adresse IP v4 valide.",
		ipv6: "Veuillez fournir une adresse IP v6 valide.",
		require_from_group: "Veuillez fournir au moins {0} de ces champs."
	});


	$('#post').submit(function(e) {
		$('table.repeater.toggle tbody').show();
		$('.hidden-input').removeClass('hidden');
	});

	var validator = $('#post').validate({
		errorClass: "invalid",
		errorPlacement: function(error, element) {
			if(element.closest('tr').hasClass('type_map')) {
				error.insertAfter(element);
			}
			else {
				error.appendTo(element.closest('td'));
			}
			element.closest('tr').find('td').addClass('error');
		},
		invalidHandler: function(event, validator) {
			$('.hidden-input').addClass('hidden');
		},
		success: function(label) {
			label.closest('tr').find('td').removeClass('error');
			label.remove();
		}
	});

	// bugfix : input="number" with Chrome
	if($('.mbc input[type="number"]')[0]) {
		$('.mbc input[type="number"]').rules('remove', "number");
	}
	/*
	// verifier que les champs
	$(".mbc input[type=text].validate-number").each(function() {
		$(this).rules('add', {
			number: true,
		});
	});

	$('.mbc input[type="number"]').focusout(function (evt) {
		if(!$.isNumeric($(this).val())){
			validator.element($(this));
				$(this).val("");
		}
	});
	*/

	var init_datepicker = function() {

		// champ "datepicker"
		$.datepicker.regional['fr'] = {
			closeText: 'Fermer',
			prevText: '&#x3c;Préc',
			nextText: 'Suiv&#x3e;',
			currentText: 'Courant',
			monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin', 'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
			monthNamesShort: ['Jan','Fév','Mar','Avr','Mai','Jun', 'Jul','Aoû','Sep','Oct','Nov','Déc'],
			dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
			dayNamesShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
			dayNamesMin: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
			weekHeader: 'Sm',
			dateFormat: 'dd/mm/yy',
			firstDay: 1,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: ''
		};
		$.datepicker.setDefaults($.datepicker.regional['fr']);

		if($('.mbc .datepicker')[0]) {
			$('.mbc table.general:not(.model_repeater) > tbody > tr > td > .datepicker').datepicker({
				changeMonth: true,
	      changeYear: true,
				showOn: 'both',
				buttonImage: mbc.mbc_plugin_url + '/img/datepicker.png',
				'buttonImageOnly':true,
				onSelect: function(date, inst) {
					var mysql_date = inst.selectedYear+'-'+(inst.selectedMonth+1 < 10 ? '0' : '')+(inst.selectedMonth+1)+'-'+(inst.selectedDay < 10 ? '0' : '')+inst.selectedDay
					$(this).siblings('input[type=hidden]').val(mysql_date);
				}
			});
		}

		if($('.mbc .datetimepicker')[0]) {
			$('.mbc table.general:not(.model_repeater) > tbody > tr > td > .datetimepicker').datetimepicker({
				changeMonth: true,
	      changeYear: true,
				showOn: 'both',
				buttonImage: mbc.mbc_plugin_url + '/img/datepicker.png',
				'buttonImageOnly':true,
				'stepMinute':5,
				currentText: 'Maintenant',
				closeText: 'Valider',
				timeOnlyTitle: 'Choisir',
				timeText: '',
				hourText: 'Heure',
				minuteText: 'Minute'
			});
		}

		if($('.mbc .timepicker')[0]) {
			$('.mbc table.general:not(.model_repeater) > tbody > tr > td > .timepicker').timepicker({
				showOn: 'both',
				buttonImage: mbc.mbc_plugin_url + '/img/ico-clock.png',
				'buttonImageOnly':true,
				'stepMinute':5,
				currentText: 'Maintenant',
				closeText: 'Valider',
				timeOnlyTitle: 'Choisir',
				timeText: '',
				hourText: 'Heure',
				minuteText: 'Minute'
			});
		}
	}


	// champ "select2"
	var init_select2 = function() {
		$.extend($.fn.select2.defaults, {
			formatNoMatches: function () { return "Aucun r&eacute;sultat trouv&eacute;"; },
			formatInputTooShort: function (input, min) { var n = min - input.length; return "Merci de saisir " + n + " caract&egrave;re" + (n == 1? "" : "s") + " de plus"; },
			formatInputTooLong: function (input, max) { var n = input.length - max; return "Merci de saisir " + n + " caract&egrave;re" + (n == 1? "" : "s") + " de moins"; },
			formatSelectionTooBig: function (limit) { return "Vous pouvez seulement s&eacute;lectionner " + limit + " &eacute;l&eacute;ment" + (limit == 1 ? "" : "s"); },
			formatLoadMore: function (pageNumber) { return "Chargement de r&eacute;sultats suppl&eacute;mentaires..."; },
			formatSearching: function () { return "Recherche en cours..."; }
		});

		$('.mbc table.general select.select2').select2({
			allowClear: true
		});

		$('.mbc table.general.model_repeater select.select2').each(function() {
			$(this).select2('destroy');
		});
	}



	var duplicate_repeater = function(e) {
		e.preventDefault();
		var model_repeater = $(this).parent().find('.model_repeater').first();
		var nb_block = model_repeater.attr('rel');
		var repeater_level = ($(this).hasClass('subrepeater')) ? 2 : 1;
		var re = new RegExp('model_repeater'+repeater_level, 'g');

		var block = model_repeater.clone();
		block.appendTo($(this).parent().find('.content_repeater').first());
		block.removeClass('model_repeater');
		block.html(block.html().replace(re, parseInt(nb_block)));
		block.removeClass('hidden');

		model_repeater.attr('rel', parseInt(nb_block)+1);
		model_repeater.attr('data-repeaterkey', model_repeater.attr('data-repeaterkey').replace('_'+parseInt(nb_block), '_'+(parseInt(nb_block)+1)));
		block.find('.type_wysiwyg').each(function() {
			if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
				var $type_textarea = $(this).next();
				var $textarea = $type_textarea.find('textarea');
				$(this).find('.wp-editor-container').empty().append($textarea);
				var textareaId = $textarea.attr('id');
				$type_textarea.remove();
				tinyMCE.execCommand( "mceAddEditor", false, textareaId);
			}
		});
		
		init_bind(false);
	}


	var remove_repeater = function(e) {
		e.preventDefault();
		if(confirm('Êtes-vous sûr de vouloir supprimer ce groupe')) {
			$(this).closest('table.repeater').fadeOut('slow', function() { $(this).remove(); });
		}
	}

	var delete_file = function(e) {
		e.preventDefault();
		$(this).parent().parent().siblings('.hidden-input').val('');
		$(this).closest('.edit_file').fadeOut(400, function() { $(this).siblings('.add_file').show(); });
	}

	var delete_file_gallery = function(e) {
		e.preventDefault();
		$(this).closest('.edit_file').remove();
	}

	var select_filter = function() {
		var filter_name = $(this).attr('filter');
		var filter_value = $(this).val();

		// si une checkbox n'est pas cochée, on lui donne la valeur 0
		if($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
			filter_value = 0;
		}

		$(this).closest('tbody').find('> tr.filter_'+filter_name).hide();
		$(this).closest('tbody').find('> tr.filter_show__'+filter_name+'__'+filter_value).show();
	}

	var toggle_tbody = function() {
		$(this).closest('table').find('> tbody').fadeToggle();
		$(this).closest('table').toggleClass('closed');
	}

	var update_repeater_title = function() {
		$(this).closest('table').find('> thead .repeater-title').html($(this).val());
	}



	// thx dude ! http://www.lenslider.com/articles/wordpress-3-5-media-uploader-tips-on-using-it-within-plugins/
	// http://phpxref.ftwr.co.uk/wordpress/nav.html?wp-includes/js/media-views.js.source.html
	// http://wordpress.stackexchange.com/questions/94964/wordpress-3-5-upload-tool-filter
	var custom_file_frame;
	/*
  var open_media_upload_old = function(e) {
  	e.preventDefault();
  	var is_multiple = $(this).hasClass('open_gallery_upload');
    var td_html = $(this).parent().parent();
		var gallery_preview = $(this).parent().find('.gallery_preview');
    var input_name = $(this).attr('id');

    // if the frame already exists, reopen it
    if(typeof(custom_file_frame) !== "undefined") {
       custom_file_frame.close();
    }

    // create WP media frame
    custom_file_frame = wp.media.frames.customHeader = wp.media({
       title: wp.media.view.l10n.addMedia,
       multiple: is_multiple,
       frame:	'post',
       //library: { type : 'image' },
       //button: { text: wp.media.view.l10n.select },
    });


    custom_file_frame.on('open', function() {
			// set to browse pane
			custom_file_frame.content.mode('browse');

			// set select on "uploaded" choice
			custom_file_frame.$el.find('.media-toolbar-secondary .attachment-filters').val('uploaded').change();

			// change label button
      custom_file_frame.$el.find('.media-button').text(wp.media.view.l10n.select);

      // hide left pane
      custom_file_frame.$el.find('.media-frame-menu').hide();
      custom_file_frame.$el.find('.media-frame-content, .media-frame-router, .media-frame-title').css('left', '0');
		});


		// close
		custom_file_frame.on('close',function() {
			// no file selected
			if(custom_file_frame.state().get('selection').length == 0) {

			}
			//else
			if(!is_multiple) {
				var attachment = custom_file_frame.state().get('selection').first().toJSON();

				var thumbnail = (typeof attachment.sizes != 'undefined') ? attachment.sizes.thumbnail.url : attachment.icon;
				td_html.find('.edit_file img').attr('src', thumbnail);
				td_html.find('input.hidden-input').attr('value', attachment.id);
				td_html.find('.edit_file').show();
				td_html.find('.add_file').hide();
			}
			else {
				var selection = custom_file_frame.state().get('selection');
				selection.map(function(attachment) {
					attachment = attachment.toJSON();
				  // Do something else with attachment object
					gallery_preview.append('<li class="edit_file"><img src="'+attachment.sizes.thumbnail.url+'" alt=""><input type="hidden" name="'+input_name+'[]" value="'+attachment.id+'">');
				});

			}
		});

    // open modal
    custom_file_frame.open();
 	}
 	*/



  var open_media_upload = function(e) {
  	e.preventDefault();
  	var is_multiple = $(this).hasClass('open_gallery_upload');
    var td_html = $(this).parent().parent();
		var gallery_preview = $(this).parent().find('.gallery_preview');
    var input_name = $(this).attr('id');
		var wp_media_post_id = wp.media.model.settings.post.id;
    var my_filter = ($(this).hasClass('image_only')) ? 'image' :''; // '', 'image', 'audio', 'video', 'application/pdf'

    // if the frame already exists, reopen it
    if(typeof(custom_file_frame) !== "undefined") {
       custom_file_frame.close();
    }

    // create WP media frame 
    mbc_options.custom_file_frame = custom_file_frame = wp.media.frames.customHeader = wp.media({
			title: wp.media.view.l10n.addMedia,
			button: { text: wp.media.view.l10n.select },
			priority	:	20,
			//className: 'media-frame mbc-media-frame',
			states : [
				new wp.media.controller.Library({
					title: wp.media.view.l10n.addMedia,
					multiple: is_multiple,
					library		:	wp.media.query( { type : my_filter, uploadedTo: wp_media_post_id } ),
					filterable	:	'all'
				})
			]
    });


    custom_file_frame.on('open', function() {
			// set to browse pane
			custom_file_frame.content.mode('browse');

			// set select on "uploaded" choice
			if(my_filter == '') {
				custom_file_frame.$el.find('.media-toolbar-secondary .attachment-filters').val('uploaded'); //.change();
			}
		});


		// close
		custom_file_frame.on('select',function() {
			if(!is_multiple) {
				var attachment = custom_file_frame.state().get('selection').first().toJSON();

				if(typeof attachment.sizes != 'undefined') {
					var thumbnail = (typeof attachment.sizes.thumbnail != 'undefined') ? attachment.sizes.thumbnail.url : attachment.sizes.full.url;				
				}
				else {
					var thumbnail = attachment.icon;
				}

				td_html.find('.edit_file img').attr('src', thumbnail);
				td_html.find('input.hidden-input').attr('value', attachment.id);
				td_html.find('.edit_file').show();
				td_html.find('.add_file').hide();
			}
			else {
				var selection = custom_file_frame.state().get('selection');
				selection.map(function(attachment) {
					attachment = attachment.toJSON();
					var thumbnail = (typeof attachment.sizes.thumbnail != 'undefined') ? attachment.sizes.thumbnail.url : attachment.sizes.full.url;				
				  // Do something else with attachment object
					gallery_preview.append('<li class="edit_file"><img src="'+thumbnail+'" alt=""><input type="hidden" name="'+input_name+'[]" value="'+attachment.id+'">');
				});

			}
		});


		custom_file_frame.on('content:activate', function(){
			// bug : ne fonctionne pas avec audio et video
			if(my_filter != 'image') {
				return false;
			}
			// vars
			var toolbar = null, filters = null;


			// populate above vars making sure to allow for failure
			try
			{
				toolbar = custom_file_frame.content.get().toolbar;
				filters = toolbar.get('filters');
			}
			catch(e) { }

			if(!filters) {
				return false;
			}

			// filter only images
			$.each( filters.filters, function( k, v ){
				v.props.type = my_filter;
			});


			// remove non image options from filter list
			filters.$el.find('option').each(function(){
				var v = $(this).attr('value');

				// don't remove the 'uploadedTo' if the library option is 'all'
				if( v == 'uploaded' && 0 /*&& t.o.library == 'all'*/ ) {
					return;
				}

				if(v != my_filter && v != 'uploaded')	{
					$(this).remove();
				}
			});

			// set default filter
			filters.$el.val('uploaded').trigger('change');
		});

    // open modal
    custom_file_frame.open();
 	}







	var init_bind = function(first_load) {

		if(first_load == true) {

			// check if field was modified (but not for option page)
			if(typeof mbc == 'undefined' || mbc.is_option_page != '1') {
				var is_form_modified = false;
				$('#poststuff').on('change', '.mbc select, .mbc input', function() { is_form_modified = true;	});
				$('#poststuff').on('keypress', '.mbc input[type=text], .mbc textarea', function(){ is_form_modified = true; });

				setTimeout(function(){
					$('#poststuff iframe').each(function() {
						$(this).contents().find('body').on('keyup', function(){
							is_form_modified = true;
						});
					});
				}, 1000);

				$('#poststuff').on('click', 'input#publish', function(){ is_form_modified = false; });

				$('#poststuff').on('click', 'input#save-post', function(){ is_form_modified = false; });
				
				// prevent "enter" keypress to set is_form_modified=true 
				$('#poststuff').on('keyup', function(event) {
					var keycode = (event.keyCode ? event.keyCode : event.which);
					if(keycode == '13'){						
						is_form_modified = false;
					}
				});
				
				window.onbeforeunload = function(){
					if (is_form_modified === true) {
						return 'Les modifications que vous avez faites seront perdues si vous changez de page.';
					}
				}
			}
		}



		$('.mbc .duplicate_repeater').unbind('click').bind('click', duplicate_repeater);
		$('.mbc .remove_repeater').unbind('click').bind('click', remove_repeater);
		$('.mbc .open_media_upload').unbind('click').bind('click', open_media_upload);
		$('.mbc .open_gallery_upload').unbind('click').bind('click', open_media_upload);
		$('.mbc .delete_file').unbind('click').bind('click', delete_file);
		$('.mbc .delete_file_gallery').unbind('click').bind('click', delete_file_gallery);
		$('.mbc select.filter, .mbc input[type="radio"].filter, .mbc input[type="checkbox"].filter').unbind('change').bind('change', select_filter);
		$('.mbc .repeater_title').unbind('keyup').bind('keyup', update_repeater_title);
		$('.mbc table.toggle thead .link').unbind('click').bind('click', toggle_tbody);

		$('.mbc .type_image .edit_file, .mbc .type_gallery .edit_file').unbind('mouseover').bind('mouseover', function() { $(this).find('.file-bar').show(); });
		$('.mbc .type_image .edit_file, .mbc .type_gallery .edit_file').unbind('mouseout').bind('mouseout', function() { $(this).find('.file-bar').hide(); });
		$('.mbc .repeater .remove_repeater').unbind('mouseover').bind('mouseover', function() { $(this).addClass('selected'); });
		$('.mbc .repeater .remove_repeater').unbind('mouseout').bind('mouseout', function() { $(this).removeClass('selected'); });

		if($('.mbc .sortable')[0]) {
			$('.mbc .sortable').sortable({
				revert: true,
				axis: 'y',
				start:function (e, ui) {
					ui.item.find('.type_wysiwyg textarea').each(function() {
						var textareaId = $(this).attr('id');
						tinyMCE.execCommand( "mceRemoveEditor", false, textareaId);
					});
				},
				stop:function(e, ui) {
					ui.item.find('.type_wysiwyg textarea').each(function() {
						var textareaId = $(this).attr('id');
						tinyMCE.execCommand( "mceAddEditor", true, textareaId);
					});
				},
				handle: 'thead td.repeater-title, td.delete'
		  });
		}

		if($('.mbc .sortable-gallery')[0]) {
			$('.mbc .sortable-gallery').sortable({
		    revert: true,
		    placeholder: 'highlight'
		  });
		}

		// script "colorpicker"
		if($('.mbc .colorpicker')[0]) {
			$('.mbc table.general:not(.model_repeater) > tbody > tr.type_color .colorpicker').minicolors();
		}

		/*
		if($('.mbc .wpcolorpicker')[0]) {
			$('.mbc table.repeater:not(.model_repeater) .wpcolorpicker, .mbc table:not(.repeater) .wpcolorpicker').wpColorPicker();
		}
		*/

		// script "datepicker" et "datetimepicker"
		if($('.mbc .datepicker')[0] || $('.mbc .datetimepicker')[0] || $('.mbc .timepicker')[0]) {
			init_datepicker();
		}

		// script "select2"
		if($('.mbc select.select2')[0]) {
			init_select2();
		}
		
		// script "tags input"
		if($('.taginput')[0]) {
			$('.mbc table.general.model_repeater .taginput').addClass('taginput_disable').removeClass('taginput');

			$('.mbc table.general .taginput').tagsInput({
				width:'auto',
				defaultText:'',
				height:'auto'
			}).removeClass('taginput');

			$('.mbc table.general.model_repeater .taginput_disable').addClass('taginput').removeClass('taginput_disable');
		}



		// à utiliser via add_action('mbc_js_init', 'my_mbc_js_init'); pour ajouter du code
		if(typeof mbc_options.init_bind == 'function') {
			mbc_options.init_bind(first_load);
		}
	}

	// init event
	$('.mbc').on('click', '.reset_datepicker', function() { $(this).siblings('input').val(''); });


	init_bind(true);






	// initialise les largeurs des colones des tableaux inline
	// pour chacun des repeater inline
	if($('.mbc .content_repeater')[0]) {
		$(this).find('.hidden').removeClass('hidden').addClass('hidden_disable');

		$('.content_repeater').each(function(k) {
			if(!$(this).find('table.model_repeater').hasClass('no-table'))
			{
				// if not has sub repeater
				if(!$(this).find('div').hasClass('content_repeater')) {

					var table_model = $(this).find('table.model_repeater tbody tr:first');
					var matrix_width = [];
					var nb_element = $('td', table_model).length;

					// pour chacune des colonnes de saisie
					$('td', table_model).each(function(i){
						width = getMatchedStyle($(this).children()[0], 'width');

						// derniere colone (suppression)
						if(width == null && i == (nb_element-1)) {
							matrix_width[i] = $(this).width();
						}
						// taille fixe
						else if(width != null && width.indexOf("px") > -1 && width != '1px') {
							matrix_width[i] = parseInt(width.replace('px', ''));
						}
						// taille variable
						else {
							matrix_width[i] = 'auto';
						}
					});

					// pour chacune des colonnes du header, on ajuste la taille si le texte du label est plus grand
					var table_header = $(this).find('table.header-inline tbody tr:first');
					$("td span", table_header).each(function(i){
						if($(this).width() > matrix_width[i]) {
							matrix_width[i] = $(this).width()+10;
						}
					});


					// pour chacune des lignes du repeater
					$('table.inline', this).each(function (j) {
						// pour chacune des colonnes, on change la taille
						$("tr td", this).each(function (i){
							if(matrix_width[i] != 'auto') {
								$(this).width(matrix_width[i]);
							}
						});
					});

					// pour chacune des lignes du repeater
					$('table.inline', this).each(function (j) {
						// pour chacune des colonnes qui sont en largeur auto, on change la taille
						$("tr td", this).each(function (i){
							if(matrix_width[i] == 'auto') {
								$(this).width($(this).width());
							}
						});
					});

					// on ajuste enfin les colonnes du header
					$('td', table_model).each(function(i){
						matrix_width[i] = $(this).width();
					});

					$("td", table_header).each(function(i){
						$(this).width(matrix_width[i]);
					});

				}
			}
		});

		$(this).find('.hidden_disable').removeClass('hidden_disable').addClass('hidden');
	}




	// active les filtres au chargement d'une page
	if($('.filtered')[0]) {
		$('.filtered').each(function() {

			var matches = $(this).attr('class').match(/filter_show__([^ ]+)__([^ ]+)/g);
			var match;

			for (index = 0; index < matches.length; ++index) {

				match = matches[index].match(/filter_show__([^ ]+)__([^ ]+)/);

				// radiobox
				if($(this).parent().find('input[type="radio"][filter="'+match[1]+'"][value="'+match[2]+'"]').attr("checked")) {
					$(this).removeClass('hidden');
				}
				// checkbox
				else if($(this).parent().find('input[type="checkbox"][filter="'+match[1]+'"][value="'+match[2]+'"]').attr("checked")) {
					$(this).removeClass('hidden');
				}
				// select
				else if($(this).parent().find('select[filter="'+match[1]+'"]').val() == match[2]) {
					$(this).removeClass('hidden');			
				}
			}
		});
	}
});




function google_places_autocomplete()
{
	var param_map = mbc.google_map.mbc_map;

  var mapOptions = {
    center: new google.maps.LatLng(param_map.lat, param_map.lng),
    zoom: parseInt(param_map.zoom),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  var map = new google.maps.Map(document.getElementById(param_map.id+'_canvas'), mapOptions);

	// ajoute le marker
  var marker = new google.maps.Marker({
    map: map,
    draggable:true,
		position: (param_map.zoom == '1' ? null : new google.maps.LatLng(param_map.lat, param_map.lng))
  });



  var input = (document.getElementById(param_map.id+'_full_address'));
  var autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.bindTo('bounds', map);



  var infowindow = new google.maps.InfoWindow();


	google.maps.event.addListener(marker, 'drag', function() {
		document.getElementById(param_map.id+'_lat').value = marker.position.lat();
		document.getElementById(param_map.id+'_lng').value = marker.position.lng();
  });

  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    infowindow.close();
    marker.setVisible(false);
    //input.className = '';
    var place = autocomplete.getPlace();
    if (!place.geometry) {
      // Inform the user that the place was not found and return.
      //input.className = 'notfound';
      return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
      map.fitBounds(place.geometry.viewport);
    } else {
      map.setCenter(place.geometry.location);
      map.setZoom(17);  // Why 17? Because it looks good.
    }

    marker.setPosition(place.geometry.location);
    marker.setVisible(true);



    var address = '';
    if (place.address_components) {
      address = [
        (place.address_components[0] && place.address_components[0].short_name || ''),
        (place.address_components[1] && place.address_components[1].short_name || ''),
        (place.address_components[2] && place.address_components[2].short_name || '')
      ].join(' ');
    }

    //infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
    infowindow.open(map, marker);

    $ = jQuery;
		//console.log(place);
    $('#'+param_map.id+'_lat').attr('value', marker.position.lat());
    $('#'+param_map.id+'_lng').attr('value', marker.position.lng());
    //$('#form-phone').attr('value', place.international_phone_number);
    //$('#form-property_name').attr('value', place.name);
    //$('#form-website').attr('value', place.website);

		if(param_map.related_id != '')
		{
			for (var i=0; i<place.address_components.length; i++)
			{
				var addr = place.address_components[i];

				if (addr.types[0] == 'street_number') {
					$('#'+param_map.related_id+'_street').attr('value', addr.long_name);
				}
				if (addr.types[0] == 'route') {
					$('#'+param_map.related_id+'_street').attr('value', $('#'+param_map.related_id+'_street').attr('value')+' '+addr.long_name);
				}
				if (addr.types[0] == 'postal_code') {
					$('#'+param_map.related_id+'_cp').attr('value', addr.long_name);
				}
				if (addr.types[0] == 'locality') {
					$('#'+param_map.related_id+'_city').attr('value', addr.long_name);
				}
				if (addr.types[0] == 'country') {
					$('#'+param_map.related_id+'_country option[value="'+addr.short_name+'"]').prop('selected', true);
				}
			}

			// add formated_adress if there is no address (ex: Lux Maldives)
			if($('#'+param_map.related_id+'_street').val() == '') {
				$('#'+param_map.related_id+'_street').val(place.formatted_address);
			}
		}
  });

}


// get the css width property (different from .width() or .css('width') )
function getMatchedStyle(elem, property) {
	// element property has highest priority
	var val = elem.style.getPropertyValue(property);

	// if it's important, we are done
	if(elem.style.getPropertyPriority(property))
		return val;

	// on Firefox and IE, getMatchedCSSRules() function doesn't exist
	if (typeof getMatchedCSSRules != 'function') {
		return '100%';
	}

	// get matched rules
	var rules = getMatchedCSSRules(elem);

	// iterate the rules backwards
	// rules are ordered by priority, highest last
	for(var i = rules.length; i --> 0;){
		var r = rules[i];

		var important = r.style.getPropertyPriority(property);

		// if set, only reset if important
		if(val == null || important){
			val = r.style.getPropertyValue(property);

			// done if important
			if(important)
				break;
		}
	}

	return val;
}
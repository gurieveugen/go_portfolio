/* -------------------------------------------------------------------------------- /

	Plugin Name: Go – Responsive Portfolio for WP
	Author: Granth
	Version: 1.0.0

	+----------------------------------------------------+
		TABLE OF CONTENTS
	+----------------------------------------------------+

    [1] SETUP & COMMON
    [2] MAIN PAGE
    [3] SUBMENU PAGE - TEMPLATE & STYLE EDITOR
    [4] SUBMENU PAGE - CUSTOM POST TYPES

/ -------------------------------------------------------------------------------- */
(function ($, undefined) {
	"use strict";
	$(function () {
		
	/* ---------------------------------------------------------------------- /
		[1] SETUP & COMMON
	/ ---------------------------------------------------------------------- */			
		
		/* Detect IE */
		var isIE = document.documentMode != undefined && document.documentMode >5 ? document.documentMode : false;
				
		var $goPortfolio=$('#go-portfolio-admin-wrap');

		/* open close panels */
		$goPortfolio.delegate('h3.hndle', 'click', function(){	
			var $this=$(this);
			
			if ($this.next('.inside').is(':visible')) {
				$this.next('.inside').slideUp().end().find('span').addClass('gwa-go-portfolio-closed');
			} else {
				$this.next('.inside').slideDown().end().find('span').removeClass('gwa-go-portfolio-closed');
			};
		});		

		
		/* Set up Colorpicker */
		var $colorPickerInput=$goPortfolio.find('.gw-gopf-colorpicker-input');
		
		if ($colorPickerInput.length) {

			if ($.fn.wpColorPicker) {
			  /* New colorpicker wp3.5+ */
			  $colorPickerInput.wpColorPicker();
			} else {
			   /* Old colorpicker */
				$colorPickerInput.each(function(index, element) {
					var $this = $(this);
					$this.wrap($('<div class="gw-gopf-colorpicker-wrap" />'))
					.closest('.gw-gopf-colorpicker-wrap').append($('<div class="gw-gopf-colorpicker" />').css('display','none'));
					$this.closest('.gw-gopf-colorpicker-wrap').find('.gw-gopf-colorpicker').farbtastic(function(color) { $this.val(color).css({'background-color':color}); });
				});
			};
			
			/* Show or hide 'old colorpicker' */
			$colorPickerInput.delegate(this, 'focus blur', function(e) {
				var $this = $(this);
				if (e.type=='focus') {
					$this.closest('.gw-gopf-colorpicker-wrap').find('.gw-gopf-colorpicker').css('display','block');
				} else {
					$this.closest('.gw-gopf-colorpicker-wrap').find('.gw-gopf-colorpicker').css('display','none');	
				};
			});
			
		};
		
		/* Show & Hide data groups */
		$goPortfolio.delegate('select[data-parent]', 'change', function(e) {
			var $this=$(this);
			$goPortfolio.find('.gw-go-portfolio-group[data-parent~="'+$this.data('parent')+'"]:visible').hide();
			$goPortfolio.find('.gw-go-portfolio-group[data-parent~="'+$this.data('parent')+'"][data-children~="'+$this.find(':selected').data('children')+'"]').show();
			$goPortfolio.find('.gw-go-portfolio-group[data-parent~="'+$this.data('parent')+'"][data-children~="'+$this.find(':selected').data('children')+'"]:visible').find('select').trigger('change');
		});
		
		$goPortfolio.delegate('.gw-go-portfolio-group-btn-select', 'change', function(e, triggered) {
			var $this=$(this),
				$btn=$this.next('.gw-go-portfolio-group-btn');
			
			$btn.data('children', $this.val());
			if (!triggered) {
				if ($btn.val()==$btn.data('label-m')) $btn.trigger('click');
			}
		});
		
		$goPortfolio.find('.gw-go-portfolio-group-btn-select').trigger('change');
		
		$goPortfolio.delegate('.gw-go-portfolio-group-btn', 'click', function(e) {
			var $this=$(this);
			console.log($this.data('children'));
			$goPortfolio.find('.gw-go-portfolio-group[data-parent~="'+$this.data('parent')+'"]:visible').hide();
			if ($this.val()==$this.data('label-o')) {
				$goPortfolio.find('.gw-go-portfolio-group[data-parent~="'+$this.data('parent')+'"][data-children~="'+$this.data('children')+'"]').show();
				$this.val($this.data('label-m'));
			} else {
				$this.val($this.data('label-o'));	
			}
			
			
		});
		
		/* checkbox list - open if child checked */
		$goPortfolio.delegate('.go-portfolio-checkbox-parent', 'click', function(){
			var $this=$(this);
			if ($this.is(':checked')) {
				$this.closest('li').find('ul input[type="checkbox"]').removeAttr('checked');
			};		
		});
		
		$goPortfolio.find('.go-portfolio-checkbox-list').each(function(index, element) {
			var $this=$(this);
			if ($this.find('input[type="checkbox"]:checked').length) {
				$this.prev().find('>span').addClass('go-portfolio-closed').end().closest('li').find('>ul').show();
			};
		});

		/* check & uncheck all checkbox */
		$goPortfolio.delegate('.go-portfolio-check-all, .go-portfolio-uncheck-all', 'click', function(e){	
			var $this=$(this);
			e.preventDefault();
			if ($this.hasClass('go-portfolio-check-all')) {
				$this.closest('li').siblings().find('>label input[type="checkbox"]').not(':checked').each(function(index, element) {
                    $(this).attr('checked','checked').trigger('click').attr('checked','checked');
                });
			} else {
				$this.closest('li').siblings().find('>label input[type="checkbox"]').removeAttr('checked');
			};
		});
		
		/* checkbox list event */
		$goPortfolio.delegate('.go-portfolio-checkbox-list input[type="checkbox"]', 'click', function(){
			var $this=$(this);
			if ($this.is(':checked')) {
				if ($this.parents('.go-portfolio-checkbox-list').length>1) {
					$this.parents('.go-portfolio-checkbox-list').each(function(index, element) {
						var $obj=$(this);
						$obj.closest('.go-portfolio-checkbox-list').prev('label').find('.go-portfolio-checkbox-parent:first').removeAttr('checked');	
					});
				};
			};			
		});
		
		$goPortfolio.delegate('.go-portfolio-checkbox-list label span', 'click', function(){
			var $this=$(this);
			if ($this.closest('label').find('input[type="checkbox"]').hasClass('go-portfolio-checkbox-parent')) { 
				if (!$this.hasClass('go-portfolio-closed')) {
				$this.addClass('go-portfolio-closed')
				.closest('li').find('.go-portfolio-checkbox-list:first').slideDown(200);
				} else {
					$this.removeClass('go-portfolio-closed')
					.closest('li').find('.go-portfolio-checkbox-list:first').slideUp(200);
				};
			};
			return false;
		});

		/* checkbox list - set opacity if no child */
		$goPortfolio.find('.go-portfolio-checkbox-list').each(function(index, element) {
			var $this=$(this);
			if ($this.find('li').length==0) {
				$this.closest('li').find('input[type="checkbox"]').removeClass('go-portfolio-checkbox-parent').next('span').css('opacity',0.5);
			};
		});		
		
		$goPortfolio.delegate('#go-portfolio-select', 'change', function() {
			var $this=$(this),
				$form=$this.closest('form'),
				$actionType=$form.find('#go-portfolio-action-type'),
				$btnEdit=$form.find('.go-portfolio-edit'),
				$btnClone=$form.find('.go-portfolio-clone'),
				$btnDelete=$form.find('.go-portfolio-delete'),
				editLabelOrig=$btnEdit.data('label-o'),
				editLabelMod=$btnEdit.data('label-m');
								
			if ($this.val()=='') {
				$btnEdit.val(editLabelOrig);
				$btnClone.hide();
				$btnDelete.hide();
			} else {
				$btnEdit.val(editLabelMod);				
				$btnClone.show();
				$btnDelete.show();
			};
		});

		$goPortfolio.find('#go-portfolio-select').trigger('change');
		
		
	/* ---------------------------------------------------------------------- /
		[2] MAIN PAGE
	/ ---------------------------------------------------------------------- */	

		var $pfForm = $goPortfolio.find('#go-portfolio-form');		
		$pfForm.delegate('.go-portfolio-edit, .go-portfolio-clone, .go-portfolio-delete', 'click', function() {
			var $this=$(this), 
				$actionType=$pfForm.find('#go-portfolio-action-type');
			if ($this.hasClass('go-portfolio-edit')) {
				$actionType.val('edit');
			} else if ($this.hasClass('go-portfolio-clone')) {
				$actionType.val('clone');
				$pfForm.submit();			
			} else if ($this.hasClass('go-portfolio-delete')) {
				var confirmQuestion = confirm($this.data('confirm'));
				if (confirmQuestion){
					$actionType.val('delete');
					$pfForm.submit();	
				};				
			};
		});
		
		/* Submit */
		$pfForm.submit(function(){
			$goPortfolio.find('.gw-go-portfolio-group-btn-select').trigger('change', true);
		})
		
		/* form ajax submit */
		$pfForm.submit(function(){
			var $this=$(this);
			if ($this.data('ajaxerrormsg')!=undefined) {
				$.ajax({  
					type: 'post', 
					url: ajaxurl,
					data: jQuery.param({ action: 'go_portfolio_plugin_menu_page', ajax: 'true' })+'&'+$this.serialize(),
					beforeSend: function () {
						$pfForm.find('input[type=submit]').attr('disabled', 'disabled');
						$pfForm.find('.submit .ajax-loading').css('visibility','visible');
					}
				}).always(function() {
						$pfForm.find('input[type=submit]').removeAttr('disabled');
						$pfForm.find('.submit .ajax-loading').css('visibility','hidden');
						$pfForm.prev('#result').remove();
				}).fail(function(jqXHR, textStatus) {
						$this.before('<div id="result" class="error"><p><strong>'+$this.data('ajaxerrormsg')+'</p></div>')
				}).done(function(data) {
					//alert(data);
					var $ajaxResponse=$('<div />', { 'class':'ajax-response', 'html' : data }),
						$ajaxResult=$ajaxResponse.find('#result').wrap('<div class="temp">');
						if ($ajaxResponse.find('#redirect').length) {
							if (!window.history.pushState) {
								window.location=$ajaxResponse.find('#redirect').html().replace(/amp;/g, '');
							} else {
								window.history.pushState('', '', $ajaxResponse.find('#redirect').html().replace(/amp;/g, ''));							
							};
						};
						$pfForm.before($ajaxResult.closest('.temp').html());
						$pfForm.prev('#result');
				});
				return false;
			};
		});	
		
		$pfForm.delegate('select[name="template"]', 'change', function() {
			var $this=$(this);
			$pfForm.find('[name="template-data"]').val($pfForm.find('[data="template-code['+$this.val()+']"]').val());
		});
		
		$pfForm.delegate('select[name="style"]', 'change', function() {
			var $this=$(this);
			$pfForm.find('[name="style-data"]').val($pfForm.find('[data="style-code['+$this.val()+']"]').val());
			if ($pfForm.find('select[data="style-effect['+$this.val()+']"]').length) {
				$pfForm.delegate('select[data="style-effect['+$this.val()+']"]').trigger('change');
			} else {
				$pfForm.find('[name="effect-data"]').val('');
			};
		});
		
		$pfForm.delegate('select[data*="style-effect"]', 'change', function() {
			var $this=$(this);
			$pfForm.find('[name="effect-data"]').val($this.val());
		});				
		
		$pfForm.delegate('.gw-go-portfolio-reset-template, .gw-go-portfolio-reset-style', 'click', function() {
			var $this=$(this);
			
			if ($this.hasClass('gw-go-portfolio-reset-template')) {
				var actionType = 'template',
					item = 'template='+$this.closest('td').find('select').val();
			} else {
				var actionType = 'style',
					item = 'style='+$this.closest('td').find('select').val();
			};
			
			$.ajax({  
				type: 'get', 
				url: ajaxurl,
				data: jQuery.param({ action: 'go_portfolio_reset_template_style' })+'&'+item,
				beforeSend: function () {
					$this.attr('disabled', 'disabled');
					$pfForm.find('[data="'+actionType+'-code['+$this.closest('td').find('select').val()+']"]').attr('disabled', 'disabled');
					$this.next('.ajax-loading').css('visibility','visible');
				}
			}).always(function() {
					$this.removeAttr('disabled');
					$pfForm.find('[data="'+actionType+'-code['+$this.closest('td').find('select').val()+']"]').removeAttr('disabled');
					$this.next('.ajax-loading').css('visibility','hidden');
			}).fail(function(jqXHR, textStatus) {
					$this.before('<div id="result" class="error"><p><strong>'+$this.data('ajaxerrormsg')+'</p></div>').delay(3000).slideUp(function(){ $(this).remove(); });
			}).done(function(data) {
				$pfForm.find('[data="'+actionType+'-code['+$this.closest('td').find('select').val()+']"]').val(data)
			});
		});				
		
	/* ---------------------------------------------------------------------- /
		[3] SUBMENU PAGE - TEMPLATE & STYLE EDITOR
	/ ---------------------------------------------------------------------- */			
		
		/* Template & Style editor */
		var $editorForm = $goPortfolio.find('#go-portfolio-editor-form');
		$editorForm.delegate('.go-portfolio-edit, .go-portfolio-reset, .go-portfolio-import, .go-portfolio-edit-item, .go-portfolio-reset-item', 'click', function() {
			var $this=$(this);
			if ($this.hasClass('go-portfolio-edit')) {
				$editorForm.find('#go-portfolio-action-type').val('edit');
				$editorForm.submit();
			} else if ($this.hasClass('go-portfolio-reset')) {
				$editorForm.find('#go-portfolio-action-type').val('reset');
				$editorForm.submit();
			} else if ($this.hasClass('go-portfolio-import')) {
				$editorForm.find('#go-portfolio-action-type').val('import');
				$editorForm.submit();								
			} else if ($this.hasClass('go-portfolio-edit-item')) {
				$editorForm.find('#go-portfolio-action-type').val('edit-item');
				$editorForm.submit();				
			} else if ($this.hasClass('go-portfolio-reset-item')) {
				$editorForm.find('#go-portfolio-action-type').val('reset-item');
				$editorForm.submit();				
			};
		});
		
		/* Trigger change on select elements */
		$goPortfolio.find('select:visible').trigger('change');		
		
	/* ---------------------------------------------------------------------- /
		[4] SUBMENU PAGE - CUSTOM POST TYPES
	/ ---------------------------------------------------------------------- */	
		
		var $cptPageForm = $goPortfolio.find('#go-portfolio-cpt-form');

		$cptPageForm.delegate('.go-portfolio-edit, .go-portfolio-clone, .go-portfolio-delete', 'click', function() {
			var $this=$(this), 
				$actionType=$cptPageForm.find('#go-portfolio-action-type');
			if ($this.hasClass('go-portfolio-edit')) {
				$actionType.val('edit');
			} else if ($this.hasClass('go-portfolio-clone')) {
				$actionType.val('clone');
				$cptPageForm.submit();			
			} else if ($this.hasClass('go-portfolio-delete')) {
				var confirmQuestion = confirm($this.data('confirm'));
				if (confirmQuestion){
					$actionType.val('delete');
					$cptPageForm.submit();	
				};				
			};
		});
	
		
	});
}(jQuery));


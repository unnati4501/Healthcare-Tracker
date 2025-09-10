$(document).ready(function () {
	$('#wellbeing_specialist').select2({
		placeholder: message.select_wellbeing_specialist,
		multiple: true,
		closeOnSelect: false,
	});
	$('#companyid').change(function () {
		$('#locationid').empty();
		$('.location_box').addClass('d-none');
		var company_id = $('#companyid').val(),
			_token = $('input[name="_token"]').val(),
			url = urls.locUrl.replace(':id', company_id),
			wellbeingUrl = urls.wellbeingUrl.replace(':id', company_id),
			options = '';
		$.get(url, {
			_token: _token
		}, function (data) {
			if (data && data.code == 200) {
				$.each(data.result, function (index, loc) {
					options += `<option value='${loc.id}'>${loc.name}</option>`;
				});
				$('#locationid').empty().append(options).val('');
				$('.location_box').removeClass('d-none');
			}
		}, 'json');
		$.get(wellbeingUrl, {
			_token: _token
		}, function (data) {
			if (data && data.code == 200) {
				options += `<option value='all'>Select All</option>`;
				$.each(data.result, function (index, loc) {
					options += `<option value='${loc.id}'>${loc.name}</option>`;
				});
				$('#wellbeing_specialist').empty().append(options).val('');
				$('#wellbeing_specialist').select2({
					placeholder: message.select_wellbeing_specialist,
					multiple: true,
					closeOnSelect: false,
				});
				setTimeout(function() {
					$('#wellbeing_specialist').find('option:not(:first)').prop('selected', true);
    				$('#wellbeing_specialist').trigger('change');
				}, 100);
			}
		}, 'json');
	});
	$('#locationid').change(function () {
		$('#wellbeing_specialist').empty();
		var company_id = $('#companyid').val(),
			location_id = $('#locationid').val(),
			_token = $('input[name="_token"]').val(),
			wellbeingUrl = urls.wbsLocationUrl.replace(':id', company_id).replace(':location', location_id),
			options = '';
		$.get(wellbeingUrl, {
			_token: _token
		}, function (data) {
			if (data && data.code == 200) {
				options += `<option value='all'>Select All</option>`;
				$.each(data.result, function (index, loc) {
					options += `<option value='${loc.id}'>${loc.name}</option>`;
				});
				$('#wellbeing_specialist').empty().append(options).val('');
				$('#wellbeing_specialist').select2({
					placeholder: message.select_wellbeing_specialist,
					multiple: true,
					closeOnSelect: false
				});
				setTimeout(function() {
					$('#wellbeing_specialist').find('option:not(:first)').prop('selected', true);
    				$('#wellbeing_specialist').trigger('change');
				}, 100);
			}
		}, 'json');
	});
	$('#wellbeing_specialist').on("select2:selecting", function (e) { 
		var data = e.params.args.data.id;
		if(data == 'all'){
			$('#wellbeing_specialist').find('option:not(:first)').prop('selected', true);
			$('#wellbeing_specialist').trigger('change');
			return false;
		}
   });
});

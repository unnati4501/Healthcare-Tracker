$(document).ready(function() {
    $(document).on('select2:select', '#multilocation', function(e) {
        var companyId = ($('#company_id').val() || 0);
        if (companieswithTeamLimit[companyId]) {
            $('#team-block').show();
            var template = $('#location-team-template').text().trim(),
                item = e.params.data,
                html = template.replace(/\:id/g, item.id).replace(":location-name", item.text);
            $('#location_list').append(html);
            $(`#employee_count_${item.id}, #possible_teams_${item.id}, #naming_convention_${item.id}`).data('id', item.id);
        }
    });
    $(document).on('select2:unselect', '#multilocation', function(e) {
        var companyId = ($('#company_id').val() || 0);
        if (companieswithTeamLimit[companyId]) {
            var item = e.params.data;
            $(`#location_team_${item.id}`).remove();
            if ($('#location_list').children().length == 0) {
                $('#team-block').hide();
            }
        }
    });
    $(document).on('keyup', '.emp-count', function(e) {
        var value = $(this).val(),
            id = ($(this).data('id') || 0),
            companyId = ($('#company_id').val() || 0),
            teamLimit = (companieswithTeamLimit[companyId] || 0);
        if (teamLimit > 0 && value != "" && $.isNumeric(value) && value >= 1 && value <= 10000) {
            $(`#possible_teams_${id}`).html(Math.ceil(value / teamLimit));
        } else {
            $(`#possible_teams_${id}`).html('-');
        }
    });
    $(document).on('keyup', '.show-suggestions', function(e) {
        var value = $(this).val(),
            id = ($(this).data('id') || 0);
        if (value != "" && $(this).valid()) {
            $(`#suggestion_${id}`).html(`e.g. ${value}-1, ${value}-2, etc..`);
        } else {
            $(`#suggestion_${id}`).empty();
        }
    });
});
jQ = new jQuery.noConflict();

var upcoming_commitments_app = new WPVM_App();


upcoming_commitments_app.commitment_delete = function(commitment_id)
{
	var that     = this;
	var action   = 'wpvm_commitment_delete';
	var ajax_url = wpvm_vars.ajax_url + '?action=' + action;
	var img_id   = '#commitment_delete_link_' + commitment_id + ' img';

	this.log('deleting commitment');
	this.log('delete_commitment ajax_url ' + ajax_url);

	this.jQ(img_id).attr('src', wpvm_vars.wpvm_url + '/images/wpspin_light.gif');

	this.jQ.ajax({
			url: ajax_url,
			method: 'post',
			data: { 'id': commitment_id },
			dataType: 'json',
			success: function (response)
			{
				that.log('get_data response ' + response);
				upcoming_commitments_app.render();
				open_opportunities_app.render();
			},
			failure: function (response)
			{	that.log('get_data FAILED response ' + response);
			}
	});
};


upcoming_commitments_app.commitment_inout = function(commitment_id)
{
	var that     = this;
	var action   = 'wpvm_commitment_inout';
	var ajax_url = wpvm_vars.ajax_url + '?action=' + action;
	var img_id   = '#commitment_inout_link_' + commitment_id + ' img';

	this.log('signing in/out of commitment');
	this.log('sign_inout_of_commitment ajax_url ' + ajax_url);

	this.jQ(img_id).attr('src', wpvm_vars.wpvm_url + '/images/wpspin_light.gif');

	this.jQ.ajax({
			url: ajax_url,
			data: { 'id': commitment_id },
			dataType: 'json',
			success: function (response)
			{
				that.log('get_data response ' + response);
				upcoming_commitments_app.render();
				prior_commitments_app.render();
			},
			failure: function (response)
			{	that.log('get_data FAILED response ' + response);
			}
	});
};




jQ(document).ready( function()
{
	upcoming_commitments_app.debug        = true;
	upcoming_commitments_app.action       = 'wpvm_my_upcoming_commitments';
	upcoming_commitments_app.templateName = 'wpvm_my_upcoming_commitments';
	upcoming_commitments_app.target       = '#wpvm_my_upcoming_commitments';

	upcoming_commitments_app.render();
});



jQ = new jQuery.noConflict();

var open_opportunities_app = new WPVM_App();


open_opportunities_app.opportunity_commit = function(opportunity_id)
{
	var that     = this;
	var action   = 'wpvm_opportunity_commit';
	var ajax_url = wpvm_vars.ajax_url + '?action=' + action;
	var img_id   = '#opportunity_commit_link_' + opportunity_id + ' img';

	this.log('commmitting to opportunity');
	this.log('commit_to_opportunity ajax_url ' + ajax_url);

	this.jQ(img_id).attr('src', wpvm_vars.wpvm_url + '/images/wpspin_light.gif');

	this.jQ.ajax({
			url: ajax_url,
			method: 'post',
			data: { 'opportunity_id': opportunity_id },
			dataType: 'json',
			success: function (response)
			{	that.log('get_data response ' + response);
				open_opportunities_app.render();
				upcoming_commitments_app.render();
			},
			failure: function (response)
			{	that.log('get_data FAILED response ' + response);
			}
	});
};



jQ(document).ready( function()
{
	open_opportunities_app.debug        = true;
	open_opportunities_app.action       = 'wpvm_my_open_opportunities';
	open_opportunities_app.templateName = 'wpvm_my_open_opportunities';
	open_opportunities_app.target       = '#wpvm_my_open_opportunities';

	open_opportunities_app.render();
});



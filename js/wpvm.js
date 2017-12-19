jQ = jQuery.noConflict();

jQ(document).ready(function()
{

});



function wpvm_submit_action_form(form, action, id)
{
	action_field = form + ' #wpvm_action';
	id_field     = form + ' #wpvm_id';

	jQ(action_field).val(action);
	jQ(id_field).val(id);

//	alert(action_field);
//	alert(id_field);

	jQ(form).submit();

}

function wpvm_submit_opportunity_action_form(action, id)
{	form = '#Opportunity_Action_Form';

	wpvm_submit_action_form(form, action, id);
}


function wpvm_submit_commitment_action_form(action, id)
{	form = '#Commitment_Action_Form';
	wpvm_submit_action_form(form, action, id);
}



/*


function wpvm_get_my_open_opportunities()
{
	var ajax_url = wpvm_vars.ajax_url + '?action=wpvm_my_open_opportunities';
	var data =
	{
		'action': 'wpvm_my_open_opportunities',
	};

	// alert(ajax_url);

	jQ.post(ajax_url, data, function(response)
	{
//		console.log(response);
		jQ('#wpvm_my_open_opportunities').html('Got this from the server: ' + response );
	});
}


function wpvm_get_my_prior_commitments()
{
	var ajax_url = wpvm_vars.ajax_url + '?action=wpvm_my_prior_commitments';
	var data =
	{
		'action': 'wpvm_my_prior_commitments',
	};

//	alert(ajax_url);

	jQ.post(ajax_url, data, function(response)
	{
		console.log(response);
//		jQ('#wpvm_my_prior_commitments').html('Got this from the server: ' + response );
	});
}

*/

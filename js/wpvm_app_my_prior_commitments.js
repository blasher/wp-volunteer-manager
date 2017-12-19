jQ = new jQuery.noConflict();

var prior_commitments_app = new WPVM_App();

jQ(document).ready( function()
{
	prior_commitments_app.action       = 'wpvm_my_prior_commitments';
	prior_commitments_app.templateName = 'wpvm_my_prior_commitments';
	prior_commitments_app.target       = '#wpvm_my_prior_commitments';

	prior_commitments_app.render();
});



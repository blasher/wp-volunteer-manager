function WPVM_App()
{
	this.jQ             = jQuery.noConflict();
	this.action         = "";
	this.data           = "";
	this.templateName   = "";
	this.templateHTML   = "";
	this.target         = "";
	this.debug          = false;
	this.wpvm_url       = wpvm_vars.wpvm_url;

	this.render = function()
	{
		this.get_data();
	};

	this.get_data = function()
	{
		var that     = this;
		var action   = this.action;
		var ajax_url = wpvm_vars.ajax_url + '?action=' + action;

		this.log('getting data from ' + ajax_url);

		this.jQ.ajax({
				url: ajax_url,
				method: 'GET',
				dataType: 'json',
				success: function (response)
				{
					that.log('get_data response ' + response);
					that.data = response;
					that.get_template();
				},
				failure: function (response)
				{	that.log('get_data FAILED response ' + response);
				}
		});
	};

	this.get_template = function ()
	{
		var that         = this;

		var templateName = this.templateName;
		var template_url = this.wpvm_url + '/templates/' + templateName + '.html';

		this.log('getting template from ' + template_url);

		this.jQ.ajax({
				url: template_url,
				method: 'GET',
				success: function (response)
				{
					that.log('get_template response ' + response);
					that.templateHTML = response;
					that.populate_template();
				},
				failure: function (response)
				{	that.log('get_template FAILED response ' + response);
				}
		});
	};

	this.populate_template = function()
	{
		var templateID   = '#' + this.templateName;
		var target       = this.target;

		this.jQ( templateID ).html( this.templateHTML );

		this.log( 'templateHTML ' + this.templateHTML );
		this.log( 'template data ' + this.data );
		this.log( 'target ' + target );

		output =  _.template( this.templateHTML ) ; 

		this.log( 'output ' + output );

		this.jQ(target).html( output ) ; 

		var templateID   = '#' + this.templateName;

		this.jQ('.wpvm_fancybox').fancybox();
	};


	this.pretty_datetime = function(datestring)
	{
		d = new Date(datestring);
		pretty_d = d.toDateString();
		pretty_d = d.toLocaleString();
//		this.get_data();
		return pretty_d;
	};

	this.log = function(data)
	{
		if(this.debug)
		{	console.log(data);
		}
	}

};

function WPVM_App()
{
	this.jQ             = jQuery.noConflict();
	this.action         = "";
	this.data           = "";
	this.templateName   = "";
	this.templateHTML   = "";
	this.target         = "";
	this.debug          = false;
	this.site_url       = wpvm_vars.site_url;
	this.wpvm_url       = wpvm_vars.wpvm_url;

	this.render = function()
	{
		var that             = this;
		var data_promise     = this.get_data();
		var template_promise = this.get_template();

		jQ.when(data_promise,
              template_promise).done( function()
		{  that.populate_template();
		});
	};

	this.get_data = function()
	{
		var that     = this;
		var action   = this.action;
		var ajax_url = wpvm_vars.ajax_url + '?action=' + action;
		var deferred = jQ.Deferred();

		this.log('getting data from ' + ajax_url);

		this.jQ.ajax({
				url: ajax_url,
				method: 'GET',
				dataType: 'json',
				success: function (response)
				{
					that.data = response;
					that.log('get_data response ' + response);
					deferred.resolve();
				},
				failure: function (response)
				{	that.log('get_data FAILED response ' + response);
					deferred.resolve();
				}
		});

		return deferred.promise();
	};

	this.get_template = function ()
	{
		var that         = this;
		var templateName = this.templateName;
		var template_url = this.wpvm_url + '/templates/' + templateName + '.html';
		var deferred     = jQ.Deferred();

		this.log('getting template from ' + template_url);

		this.jQ.ajax({
				url: template_url,
				method: 'GET',
				success: function (response)
				{
					that.templateHTML = response;
					that.log('get_template response ' + response);
					deferred.resolve();
				},
				failure: function (response)
				{	that.log('get_template FAILED response ' + response);
					deferred.resolve();
				}
		});

		return deferred.promise();
	};

	this.populate_template = function()
	{
		var that         = this;
		var templateID   = '#' + this.templateName;
		var templateHTML = this.templateHTML;
		var data         = this.data;
		var target       = this.target;

		this.jQ( templateID ).html( templateHTML );

		this.log( 'templateHTML ' + templateHTML );
		this.log( 'template data ' + data );
		this.log( 'target ' + target );

		output =  _.template( templateHTML ) ; 

		this.log( 'output ' + output );

		this.jQ(target).html( output ) ; 

		this.jQ('.wpvm_fancybox').fancybox();
	};


	this.pretty_datetime = function(datestring)
	{
		d = new Date(datestring);
		pretty_d = d.toDateString();
		pretty_d = d.toLocaleString();
		return pretty_d;
	};

	this.log = function(data)
	{
		if(this.debug)
		{	console.log(data);
		}
	}

};

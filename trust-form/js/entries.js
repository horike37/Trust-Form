var trustFormEntry;
(function(a){
	trustFormEntry =
	{
		init:function()
		{
			a('select[name=select_form]').bind('change', function(){
				a('#entry-form').prop( 'action', location.protocol+'//'+location.host+location.pathname+'?page=trust-form-entries&form='+a('select[name=select_form]').children('option:selected').val()+'&status=new' );
				a('#entry-form').submit();
			})
		},
	},
	a(document).ready(function ()
    {
        trustFormEntry.init();
    })
})(jQuery);
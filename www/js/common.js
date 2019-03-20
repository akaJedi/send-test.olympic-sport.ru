$(function(){

	$('.action-delete').click(function(event){
		event.preventDefault();
		var row = $(this).parent().parent();
		row.css('background','yellow');
		if (confirm('вы действительно хотите удалить \n"'+$(this).attr('data-customer')+'" ?')) 
		{			
			$.ajax({
				url: $(this).attr('href'),
				type: 'GET',
			}).done(function(data){
				if(data=='success'){
					row.css('background','rgb(255, 118, 118)').fadeOut('slow', function() { $(this).remove(); });
				}else{
					alert(data);
				}
			}).fail(function(jqXHR, textStatus) {
			  alert( "Request failed: " + textStatus );
			});
		}else{
			row.css('background','none');
		}
	});

});
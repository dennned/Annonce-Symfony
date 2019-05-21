$(function(){
	var location = window.location.href;
	var cur_url = '/' + location.split('/').slice(-2, -1)+'/'+location.split('/').slice(-1);

	// alert(cur_url);
	$('.navbar-nav li').each(function(){
		var link = $(this).find('a').attr('href');
		if(cur_url == link) {
			$(this).addClass('active');
		}

	});
});


$('.sort_id').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.id').text(),
            bn = $(b).find('.id').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});


$('.sort_name').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.name').text(),
            bn = $(b).find('.name').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});

$('.sort_country').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.country').text(),
            bn = $(b).find('.country').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});


$('.sort_parent').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.parent').text(),
            bn = $(b).find('.parent').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});

$('.sort_date').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.date').text(),
            bn = $(b).find('.date').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});

$('.sort_category').click(function(){
    var elements = $('.list-li');
    var target = $('.list-body ul');

    elements.sort(function (a, b) {
        var an = $(a).find('.category').text(),
            bn = $(b).find('.category').text();
        
        if (an && bn) {
            return an.toUpperCase().localeCompare(bn.toUpperCase());
        }
        
        return 0;
    });
    
    elements.detach().appendTo(target);
});

// $('.delete_row').click(function(){
//     return confirm("Are you sure you want to delete?");
// });




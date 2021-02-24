function sticky_table() {
	
    $(".table-sticky").each(function(){

        var _table = $(this);
        var _tableh = _table.find("thead").find("tr").clone().appendTo(_table);
        var _tabler = _table.find("tbody").find("tr").first();
        var _tablerd = _tabler.find("td");
        var _tablehd = _tableh.find("th");
        var offset, left, wtop, bottom, right;
        
        function resize(){
            offset = _table.offset(),
            left = offset.left,
            wtop = offset.top,
            bottom = wtop + _table.height(),
            right = ($(window).width() - (left + _table.width()));
            _tableh.css({
                "top" : 0,
                "left": left,
                "right": right,
                "background": "#fff"
            });
            _tablerd.each(function(i){
                var width = $(this).outerWidth(),
                        height = $(this).outerHeight();
                _tablehd.eq(i).css({
                    "width": width,
                    "height": (height > 40 ? 40 : height),
                });
            });
        }
        function attachScroll(){
			
            $(window).scroll(function(){
                if($(this).scrollTop() > bottom){
					
                    _tableh.css({
                        "position": "static",
                        "visibility": "collapse"
                    });
                }
                else if($(this).scrollTop() > wtop){
					resize();
					_tableh.css({
                        "position": "fixed",
                        "visibility": "visible"
                    });
                }
                else{
                    _tableh.css({
                        "position": "static",
                        "visibility": "collapse"
                    });
                }
            });
        }

        resize();
        attachScroll();
        $(window).resize(function(){
            resize();
            attachScroll();
        });
    });
}
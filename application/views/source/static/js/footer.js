$(function(){
	$('.foot_tabber .foot_tabbar_item').click(function() {
			$(this).addClass('foot_tabbar_active').siblings().removeClass('foot_tabbar_active');
		})

	
           //	发布底部弹出框
    $(".publish").click(function(){
    	$(".eject_bottom").show(500);
    })
    $(".cancel").click(function(){
    	$(".eject_bottom").hide(300);
    })


	
})

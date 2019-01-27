$(function(){
	//地址选择器
	$("#start_city").cityPicker({
			title: "出发地"
		});
		$("#reach_city").cityPicker({
			title: "目的地"
		});
		
	//筛选层出现事件
	$(".main_head .choise").click(function(){
		$(".mskeLayBg").show();
		$(".choise_click").show();
	})
	
	//筛选层消失事件
	$(".choise_click .head_right").click(function(){
		$(".mskeLayBg").hide();
		$(".choise_click").hide();
	})
	$(".choise_click .choise_result").click(function(){
		$(".mskeLayBg").hide();
		$(".choise_click").hide();
	})
})

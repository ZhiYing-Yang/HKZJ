$(function(){
	
  $("#start_city").cityPicker({
    title: "请选择出发地"
  });
  
  $("#arrive_city").cityPicker({
    title: "请选择到达地"
  });
  $("#hold_time").datetimePicker();
  
  //发布须知出现事件
	$(".pu_toknow").click(function(){
		$(".mskeLayBg").show();
		$(".page_know").show();
	})
	$(".sure_publish span").click(function(){
		$(".mskeLayBg").show();
		$(".page_know").show();
	})
	//筛选层消失事件
	$(".sure_know").click(function(){
		$(".mskeLayBg").hide();
		$(".page_know").hide();
	})
	
	$.toast('.input_submit');
    $.toast("操作成功");
})

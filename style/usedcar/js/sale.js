

$(".extime").picker({
  cols: [
    {
      values: ['2018', '2019', '2020', '2021', '2022', '2023', '2024', '2025', '2026', '2027', '2028', '2029', '2030', '2031', '2032', '2033', '2034', '2035', '2036', '2037', '2038', '2039', '2040', '2041', '2042', '2043', '2044', '2045', '2046', '2047','2048']
    },
    {
      values: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']
    }
  ]
});

$(".retime").picker({
  cols: [
    {
      values: ['1988', '1989', '1990', '1991', '1992', '1993', '1994', '1995', '1996', '1997', '1998', '1999', '2000', '2001', '2002', '2003', '2004', '2005', '2006', '2007', '2008', '2009', '2010', '2011', '2012', '2013', '2014', '2015', '2016', '2017','2018']
    },
    {
      values: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']
    }
  ]
});

 $("#city-picker").cityPicker({
 	showDistrict: false
 });
 
 $(".smodel").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['牵引车','载货车','自卸车','轻车','挂车','搅拌车','专用车']
    }
  ]
});

$(".estandard").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['国二','国三','国四','国五','其他']
    }
  ]
});
$(".gears").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['5','6','7','8','9','10','12','13','14','16','其他']
    }
  ]
});
$(".driving_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['4*2','6*2','6*4']
    }
  ]
});
$(".load_driving_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['4*2','6*2','6*4','8*2','8*4']
    }
  ]
});
$(".driving_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['4*2','6*2','6*4']
    }
  ]
});
$(".mixer_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['4*2','6*2','6*4','8*4']
    }
  ]
});
$(".speed_ratio").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['2.86','3.08','4.11','4.44','4.875','5.275','其他']
    }
  ]
});
$(".driving_crate").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['厢式','仓棚式','栏板式']
    }
  ]
});
$(".trailer_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['仓棚式','栏板式','集装箱','厢式','低平板式','其他']
    }
  ]
});
$(".number_axles").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['1','2','3','其他']
    }
  ]
});
$(".suspension_form").picker({
  cols: [
    {
    	textAlign: 'center',
      values: ['钢板','气囊','其他']
    }
  ]
  });

//弹出页面输入标题 和 描述
$('.succeed').click(function(){
    var y=$('.headline textarea').val();
    var z=$('.postscript textarea').val();
    $('.ex_headline input').val(y);
    $('.ex_postscript input').val(z);
    $('html, body').animate({scrollTop: $('html').height()}, 0);
});


//checked
$('input[name="radio"]').on('change', function(){
    if($('input[name="radio"]:checked').val() == 1){
        $('.checked').css('display', 'block');
    }else{
        $('.checked').css('display', 'none');
    }
});

//if(isChecked==true)
//{
//	$(".checked").show();
//}
// if($('.choose').attr('checked', true)){
// 	$(".checked").show();
// };

//$('.choose').click(function(){
//	$(".checked").css("display", "block");
//	});

function typeChange() {
     window.x = $(".smodel").val();
     $(".common").css("display", "none");
  switch(x){
    case '牵引车':
    	document.getElementsByClassName('motor_tractor')[0].style.display="block";
    	break;
    case '载货车':
        document.getElementsByClassName('load_vehicle')[0].style.display="block";
    	break;
    case '自卸车':
        document.getElementsByClassName('motor_tractor')[0].style.display="block";
    	break;
    case '轻车':
        document.getElementsByClassName('light_truck')[0].style.display="block";
    	break;
    case '挂车':
        document.getElementsByClassName('trailer')[0].style.display="block";
    	break;
    case '搅拌车':
        document.getElementsByClassName('mixer_truck')[0].style.display="block";
    	break;
    case '专用车':
        document.getElementsByClassName('special_vehicle')[0].style.display="block";
    	break;
    }
}
$('.weui_switch').click(function(){
    if($(this).val() == 'on'){
        $(this).val('off');
    }else{
        $(this).val('on');
    }
});

//压缩图片
function compressImage(img, fileSize, quality) {

    quality = quality || 0.2;
    if(fileSize > 1){ //大于1M 对图片进行压缩
        var canvas = document.createElement("canvas");
        var ctx = canvas.getContext('2d');
        var tCanvas = document.createElement("canvas");
        var tctx = tCanvas.getContext("2d");
        var initSize = img.src.length;
        var width = img.width;
        var height = img.height;
        var ratio;
        if ((ratio = width * height / 4000000) > 1) {
            ratio = Math.sqrt(ratio);
            width /= ratio;
            height /= ratio;
        } else {
            ratio = 1;
        }
        canvas.width = width;
        canvas.height = height;
        ctx.fillStyle = "#fff";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        var count;
        if ((count = width * height / 1000000) > 1) {
            count = ~~(Math.sqrt(count) + 1);
            var nw = ~~(width / count);
            var nh = ~~(height / count);
            tCanvas.width = nw;
            tCanvas.height = nh;
            for (var i = 0; i < count; i++) {
                for (var j = 0; j < count; j++) {
                    tctx.drawImage(img, i * nw * ratio, j * nh * ratio, nw * ratio, nh * ratio, 0, 0, nw, nh);
                    ctx.drawImage(tCanvas, i * nw, j * nh, nw, nh);
                }
            }
        } else {
            ctx.drawImage(img, 0, 0, width, height);
        }
        var data = canvas.toDataURL('image/jpeg', quality);
        tCanvas.width = tCanvas.height = canvas.width = canvas.height = 0;
        return data;
    }else{
        return img.src;
    }

}

var url = 'http://www.jxhkzj.com/usedcar/sale';
$('.publish').click(function(){
    var _this = $(this);
    if(_this.attr('disabled') == 'disabled'){
        $.toast("请勿重复提交", "forbidden");
        return;
    }
    _this.attr('disabled', 'disabled'); //设为已提交状态
    $.showLoading("正在提交...");
    //车辆展示图片
    if($('#uploaderFiles').find('li').length == 0){
        $.hideLoading();
        $.toptip('请上传车辆图片 以获得更好的展示', 'error');
        _this.attr('disabled', '');
        return;
    }
    var img_arr_str = '';
    var img_arr = new Array();
    for(var i = 0, len = $('#uploaderFiles').find('li').length; i<len; i++){
        img_arr[i]= $('#uploaderFiles').find('li').eq(i).attr('data-url');
    }
    img_arr_str = img_arr.join('-$-');

    //行驶证照片
    if($('#license_img').length == 0){
        $.hideLoading();
        $.toptip('请上传该车辆的行驶证照片', 'error');
        _this.attr('disabled', '');
        return;
    }
    var license_img = $('#license_img').attr('data-url');

    //车型
    var car_type = $.trim($('input[name="smodel"]').val());
    if(car_type.length == 0){
        $.hideLoading();
        $.toptip('请选择车型', 'error');
        _this.attr('disabled', '');
        return;
    }

    var car_type_index = 0;
    for(var i = 0, len = $('.common').length; i < len; i++){ //获得待完善参数的index
        if($('.common').eq(i).css('display') == 'block'){
            car_type_index = i;
        }
    }
    //车辆参数
    var parameter_input = $('.common').eq(car_type_index).find('input');
    var parameter_arr = new Array("", "", "", "", "", "", "", "");
    for(var i = 0, len = parameter_input.length; i < len; i++){
        if($.trim(parameter_input.eq(i).val()).length == 0){
            console.log('参数'+$.trim(parameter_input.eq(i).val()));
            $.hideLoading();
            $.toptip('请完善车辆参数', 'error');
            parameter_input.eq(i).focus();
            _this.attr('disabled', '');
            return;
        }
        parameter_arr[i] = $.trim(parameter_input.eq(i).val());
    }

    //车牌
    var brand = $.trim($('input[name="brand"]').val());
    if(brand.length == 0){
        $.hideLoading();
        $.toptip('请输入您的车辆品牌');
        $('input[name="brand"]').focus();
        _this.attr('disabled', '');
        return;
    }

    //车辆所在地
    var address = $.trim($('#city-picker').val());
    if(address.length == 0){
        $.hideLoading();
        $.toptip('请选择车辆所在地', 'error');
        _this.attr('disabled', '');
        return;
    }

    //是否提供挂靠
    var guakao = $('input[name="guakao"]').val() == 'on'?1:0;

    //是否可以过户
    var guohu = $('input[name="guohu"]').val() == 'on'?1:0;

    //表里程
    var bxlc = $.trim($('input[name="distance"]').val());
    if(bxlc.length == 0){
        $.hideLoading();
        $.toptip('请输入表显里程', 'error');
        $('input[name="distance"]').focus();
        _this.attr('disabled', '');
        return;
    }

    //全款价格
    var whole_price = $.trim($('input[name="whole_price"]').val());
    if(whole_price.length == 0){
        $.hideLoading();
        $.toptip('请输入全款价格', 'error');
        $('input[name="whole_price"]').focus();
        _this.attr('disabled', '');
        return;
    }

    //付款方式
    var pay_type = $('input[name="radio"]:checked').val();
    pay_type = (pay_type==1 || pay_type ==0) ? pay_type : 0;
    //分期付款
    var down_payment = '';
    if(pay_type == 1){
        down_payment = $.trim($('input[name="down_payment"]').val());
        if(down_payment.length == 0){
            $.hideLoading();
            $.toptip('请输入首付价格', 'error');
            $('input[name="down_payment"]').focus();
            _this.attr('disabled', '');
            return;
        }
    }

    //行驶证等级日期
    var xszdjrq = $.trim($('input[name="xszdjrq"]').val());
    if(xszdjrq.length == 0){
        $.hideLoading();
        $.toptip('请输入行驶证登记日期', 'error');
        $('input[name="xszdjrq"]').focus();
        _this.attr('disabled', '');
        return;
    }

    //交强险过期时间
    var jqxgqsj = $.trim($('input[name="jqxgqsj"]').val());
    if(jqxgqsj.length == 0){
        $.hideLoading();
        $.tiptop('请输入交强险过其时间', 'error');
        $('input[name="jqxgqsj"]').focus();
        _this.attr('disabled', '');
        return;
    }

    //标题
    var title = $.trim($('textarea[name="title"]').val());
    if(title.length == 0){
        $.hideLoading();
        $.toptip('请输入标题', 'error');
        _this.attr('disabled', '');
        return;
    }

    //描述
    var postscript = $.trim($('textarea[name="postscript"]').val());
    if(postscript.length == 0){
        $.hideLoading();
        $.toptip('请输入描述', 'error');
        _this.attr('disabled', '');
        return;
    }

    //验证码
    var authcode = $.trim($('input[name="authcode"]').val());
    if(authcode.length == 0){
        $.hideLoading();
        $.toptip('请输入验证码', 'error');
        $('input[name="authcode"]').focus();
        _this.attr('disabled', '');
        return;
    }

    var data = {img_arr_str: img_arr_str, brand: brand, license_img: license_img, car_type: car_type, address: address, guakao: guakao, guohu: guohu, bxlc: bxlc, whole_price: whole_price,
        pay_type: pay_type, down_payment: down_payment, xszdjrq: xszdjrq, jqxgqsj: jqxgqsj,
        parameter0: parameter_arr[0], parameter1: parameter_arr[1], parameter2: parameter_arr[2], parameter3: parameter_arr[3], parameter4: parameter_arr[4], parameter5: parameter_arr[5], parameter6:parameter_arr[6], parameter7: parameter_arr[7],
        title: title, postscript: postscript, authcode: authcode
    };

    $.post(url, data, function(data){
        if(data.code == 200){
            $.hideLoading();
            $.toast('发布成功', function(){
                window.location.href = '/usedcar/index';
            });
        }else{
            $.hideLoading();
            $.toptip(data.message, 'error');
            _this.attr('disabled', '');
        }
    }, 'json');

});
    
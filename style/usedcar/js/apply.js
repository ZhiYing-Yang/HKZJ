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

//上传图片
$('input[type="file"]').on('change', function(e){
    $.showLoading('正在上传');
    var _this = $(this);
    var info = _this.next().html(); //p标签里的内容
    var parent = _this.parent();
    var grandpa = parent.parent();
    var file = e.target.files[0];
    var img = new Image();
    var reader = new FileReader();

    reader.onload = function(f){
        img.src = f.target.result;
        setTimeout(function(){
            var imageData = compressImage(img, Math.ceil(file.size / 1024 / 1024));

            $.post('/usedcar/uploadImage', {image: imageData}, function(data){
                if(data.code == 200){
                    var src = data.data.path;
                    parent.remove();

                    grandpa.append('<li><img class="upload_img" src="'+ imageData +'" data-url="'+ src +'" alt=""><p class="note">'+ info +'</p></li>')

                    $.hideLoading();
                }else{
                    $.hideLoading();
                    $.toptip(data.message, 'error');
                }
            }, 'json');
        }, 10);
    };
    reader.readAsDataURL(file);
});

//提交表单
$(".submit_btn").click(function(e) {
    e.preventDefault();
    $.showLoading('正在提交');
    var url = '/usedcar/apply/'+$(this).attr('data-type');
    //验证码
    var authcode = $.trim($('input[name="authcode"]').val());
    if(authcode.length != 4){
        $.toptip('请输入正确的验证码', 'error');
        $('input[name="authcode"]').focus();
        $.hideLoading();
        return;
    }

    //真实姓名
    var realname = $.trim($('input[name="realname"]').val());
    if(realname.length == 0){
        $.toptip('请输入姓名', 'error');
        $('input[name="realname"]').focus();
        $.hideLoading();
        return;
    }

    //所在城市
    var address = $.trim($('input[name="address"]').val());
    if(address.length == 0){
        $.toptip('请选择所在城市', 'error');
        $.hideLoading();
        return;
    }

    //身份证号
    var ID_card = $.trim($('input[name="ID_card"]').val());
    if(ID_card.length != 18){
        $.toptip('请输入正确的身份证号', 'error');
        $('input[name="ID_card"]').focus();
        $.hideLoading();
        return;
    }

    //卖车手机号
    var phone = $.trim($('input[name="phone"]').val());
    if(phone.length == 0){
        $.toptip('请输入卖车手机号', 'error');
        $('input[name="phone"]').focus();
        $.hideLoading();
        return;
    }

    //微信号
    var we_chat  = $.trim($('input[name="we_chat"]').val());
    if(we_chat.length == 0){
        $.toptip('请输入微信号', 'error');
        $('input[name="phone"]').focus();
        $.hideLoading();
        return;
    }

    //身份证正面照
    var img0 = $('.upload_img').eq(0).attr('data-url');
    if(!img0 || img0.length == 0){
        $.toptip('请上传身份证正面照', 'error');
        $.hideLoading();
        return;
    }

    //身份证背面照
    var img1 = $('.upload_img').eq(1).attr('data-url');
    if(!img1 || img1.length == 0){
        $.toptip('请上传身份证背面照', 'error');
        $.hideLoading();
        return;
    }

    //手持身份证照片
    var img2 = $('.upload_img').eq(2).attr('data-url');
    if(!img2 || img2.length == 0){
        $.toptip('请上传手持身份证照', 'error');
        $.hideLoading();
        return;
    }
    var data = {authcode: authcode, realname: realname, address: address, ID_card: ID_card, phone: phone, we_chat: we_chat, img0: img0, img1: img1, img2: img2};
    if($(this).attr('type') == '商家'){
        //营业执照
        var img3 = $('.upload_img').eq(3).attr('data-url');
        if(!img3 || img3.length == 0){
            $.toptip('请上传营业执照', 'error');
            $.hideLoading();
            return;
        }

        //商家类型
        var merchant_type = $.trim($('input[name="merchant_type"]').val());
        if(merchant_type.length == 0){
            $.toptip('请选择商家类型', 'error');
            $.hideLoading();
            return;
        }

        //公司名称
        var company_name = $.trim($('input[name="company_name"]').val());
        if(company_name.length == 0){
            $.toptip('请输入公司名称', 'error');
            $('input[name="company_name"]').focus();
            $.hideLoading();
            return;
        }

        //有效期至
        var indate = $.trim($('input[name="indate"]').val());
        if(indate.length == 0){
            $.toptip('请选择有效期至', 'error');
            $.hideLoading();
            return;
        }

        //注册号
        var registration_number = $.trim($('input[name="registration_number"]').val());
        if(registration_number.length == 0){
            $.toptip('请输入注册号', 'error');
            $('input[name="registration_number"]').focus();
            $.hideLoading();
            return;
        }

        //公司地址
        var company_address = $.trim($('input[name="company_address"]').val());
        if(company_address.length == 0){
            $.toptip('请输入公司地址', 'error');
            $('input[name="company_address"]').focus();
            $.hideLoading();
            return;
        }

        if($('#checkbox').checked){
            $.toptip('阅读服务条款并勾选同意', 'error');
            return;
        }

        var data1 = {company_type: merchant_type, merchant_name: company_name, indate: indate, registration_number: registration_number, company_address: company_address, img3: img3};
        $.extend(data, data1);
    }

    $.post(url, data, function(data){
        if(data.code == 200){
            $.hideLoading();
            $.toast("操作成功", function(){
                location.href = '/usedcar/person';
            });
        }else{
            $.hideLoading();
            $.toast(data.message, 'cancel');
            $('.authcode_img').click();
        }
    }, 'json');

});
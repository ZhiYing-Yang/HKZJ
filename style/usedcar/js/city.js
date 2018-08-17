$(function(){
    $("#select-adress").click(function(){
        $(".select-city").css("display","block")
        $(".select-city").animate({"left":"0"},200);
        
    });
    $("#city-return").click(function(){
        $(".select-city").animate({"left":"100%"},200 ,function(){
            $(".select-city").css("display","none")
        })
    });
    $(".select-city .container p").click(function(){
        $(".select-city input[type=city]").val($(this).text());
        $(".select-city").scrollTop(0);
        $("#select-adress .adress" ).text($(this).text());
         $("#city-return").click();
    }); 
    $(".letter a").click(function(){
         $.toast("纯文本", "text");
    })

})


$(function(){
    
//  导航点击事件
    $('.main_head .element').click(function(){
        var element_index=$('.main_head .element').index(this);
        $('.choice_form').eq(element_index).animate({"margin-left":"25%"}, 200);
        $('.mask_layer').show();
    })
    
//  左上角图标点击关闭事件
    $('.choice_form span').click(function(){
        $('.choice_form').css('margin-left','100%');
        $('.mask_layer').css('display','none');
    }) 
    
//  阴影点击关闭事件
    $('.mask_layer').click(function(){
        $('.choice_form').animate({"margin-left":"100%" } ,200);
        $('.mask_layer').css('display','none');
    })
    var flag =true;
//  选中事件
    $('.choice_form label').click(function(event){
        $('.choice_form').css('margin-left','100%');
        $('.mask_layer').css('display','none');
        if(flag){
            
            $(".check-option-box .checked-items").append(
                $("<span class='checked-item weui-col-40' >"+$(this).find("p").text()+"<i id='delete-check-item'>×</i> </span>")
            )
        }
        flag=!flag; 
        $("#replace").css("display","block")
        $(".checked-items .checked-item:last-child").click(function(){
            $(this).remove();
            (function(){
                if($(".checked-items").children().length==0){
                    $("#replace").css("display","none");
                }
            })();
        })
    })
    $("#replace").click(function(){
        $(".check-option-box .checked-items").children().remove();
        $(this).css("display","none");
    })
    
})

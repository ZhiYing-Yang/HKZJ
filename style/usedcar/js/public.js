//格式化时间
function formatDate(date){
    //console.log(date);
    var date = new Date(date*1000);
    var Y = date.getFullYear() + '.';
    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '.';
    var D = date.getDate() + ' ';
    return (Y+M+D);
}

//遍历输出数据
function setDataList(data, box){
    var html='';
    for(var i = 0, len = data.length; i < len; i++){
        html+= '<div class="lovebox">';
        html+= '<div class="loves">';
        html+= '<a href="'+ '/usedcar/see/'+ data[i].id +'" class="weui_cell  love">';
        html+='<div class="weui_cell_hd thumb">';
        html+='<img src="'+ data[i].img_arr_str.split('-$-')[0] +'" alt="icon">';
        html+='</div>';
        html+='<div class="weui_cell_bd">';
        html+='<div class="message">';
        html+='<span class="car-type">'+ data[i].car_type +'&emsp;</span>';
        html+='<span class="car-name">'+ data[i].title +'</span>';
        html+='</div>';
        html+='<div class="other"><span class="price">'+ data[i].whole_price +'万</span></div>';
        html+='<div>';
        html+='<span class="date text-grey">'+ formatDate(data[i].create_time) +'</span><span class="adress text-grey">'+ data[i].address +'</span>';
        html+='</div>';
        html+='</div>';
        html+='</a>';
        html+='</div>';
        html+='</div>';
    }
    box.append(html);
}

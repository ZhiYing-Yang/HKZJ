/*常用正则表达式*/
function phoneReg(str){
    var my_reg=/^[1][3,4,5,7,8][0-9]{9}$/;
    if(my_reg.test(str)){
        return true;
    }else{
        return false;
    }
}
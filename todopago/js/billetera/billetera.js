function checkBilleteraValue(selector){
    if(!bannerSelector(1) && !bannerSelector(2) && !bannerSelector(3)){
        $(selector).attr("checked","checked");
    }
}
function bannerSelector(selector){
    return $("#banner"+selector).is(":checked");;
}

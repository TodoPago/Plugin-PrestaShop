function check_last_version(actualVersion)
{
    $.ajax({
            type: "GET",
            accepts: "application/json",
            dataType: 'json',
            url: "https://api.github.com/repos/TodoPago/Plugin-PrestaShop/releases/latest",
            beforeSend: function(xhr){
                xhr.setRequestHeader("Authorization",'token 21600a0757d4b32418c54e3833dd9d47f78186b4');
            },
            success: function(data){
                var lastVersion=data.tag_name.replace("V","");
                check_version(actualVersion,lastVersion);
        },
        error: function(data){
           error_response = JSON.stringify(data);
           console.log(error_response);
           return error_response;
        }
    });
}

function check_version(actualVersion,lastVersion)
{   
    var result=versionCompare(actualVersion,lastVersion);
    
    if(result=="-1"){
        $("#panel_actualizacion_disponible").show(500);
    }    
}

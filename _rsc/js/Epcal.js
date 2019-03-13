/*global $:false, console:false quotmark:true */
"use strict";
var Epcal = (function(){
    
    var deleteProgramHandler = function () {
        console.log("start delete program");
        deleteGeneric(this);
        return false;
    };

    var addProgramHandler = function () {
        console.log("start add program");

        $.ajax({
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            url: $(this).attr("action"),
            data: (function() {
                return JSON.stringify({
                    "title": $("#add_title").val(),
                    "epguide_title": $("#add_epguide_title").val(),
                    "query_code": $("#add_query_code").val()
                    });
            })(),
            error:   ajaxErrorHandler,
            success: addProgramRow
        });
        return false;
    };
    var addProgramRow = function (jsonObj) {
        console.log("start add program row");
      if(jsonObj === null || jsonObj.status == "No"){
        console.log("obj is null");
        
        showStatusMessage("Data not Saved due to Sql Error: ");
      }else{
        console.log("program added");
        showStatusMessage(jsonObj.id + " add complete");
        // TODO: ADD row
        //add new row
        var newRow = "<td>"+jsonObj.title+"</td>";
        newRow = newRow + "<td colspan=\"5\"><a href=\""+jsonObj.id+"/define/\">load from epguide</a></td>";
        $("#programs_table tr:last").after("<tr>"+ newRow +"</tr>");
      }
    };
    var updateProgramHandler = function () {
      console.log("start update program");
      var $elementTarget = $(this);

      console.log  ($elementTarget.attr("action"));
      $.ajax({
        type: "PUT",
        contentType: "application/json",
        dataType: "json",
        url: $elementTarget.attr("action"),
        data: (function() {
            var values = {};
            $.each($elementTarget.serializeArray(), function(i, field) {
                values[field.name] = field.value;
            });
            console.log(values);
            return JSON.stringify(values);
        })(),
        error: ajaxErrorHandler,
        success: function(jsonObj){
           if(jsonObj === null || jsonObj.status == "No"){
                console.log("obj is null");
                showStatusMessage("Data not Saved due to Sql Error: ");
                
           }else{
                showStatusMessage(jsonObj.id+ " updated");
              
           }
        }
      });
      return false;
    };
    var addEpisode = function () {
        console.log("start add episode");

        $.ajax({
        type: "POST",
        dataType: "json",
        url: $(this).attr("action"),
        data: (function() {
            return JSON.stringify({
                "program_id" : $("#program_id").val(),
                "title" : $("#add_title").val(),
                "airdate" : $("#add_airdate").val()
                });
        })(),
        accept: {
          json: "application/json"
        },
        error: ajaxErrorHandler,
        success: addEpisodeRow
        });
        return false;
    };
    var addEpisodeRow = function (jsonObj) {
      // var jsonObj = jQuery.parseJSON(results);
            
      if(jsonObj === null){
        console.log("obj is null");
        
        showStatusMessage("Data not Saved due to Sql Error: ");
      }else{
        showStatusMessage("Episode added");
        
        console.log("episode added");

        //add new row
        var newRow = "<td>s0e0</td>";
        newRow = newRow + "<td>"+jsonObj.title+"</td>";
        newRow = newRow + "<td>"+jsonObj.airdate+"</td>";
        newRow = newRow + "<td><ul><li><a href=\"/epcal2/index.php/episodes/" + jsonObj.id + "/sendtocalendar\">";
        newRow = newRow + "<i class=icon-calendar></i> Send to Calendar</a></li>";
        newRow = newRow + "<li><a href=\"/epcal2/index.php/torrents/?search_string=" + jsonObj.title + "\" > Torrent list</a></li>";
        newRow = newRow + "</ul></td>";
        
        $("#episodes_table tr:last").after("<tr>"+ newRow +"</tr>");
      }
    };
    var deleteProgramEpisode = function () {
        console.log("start delete program episode");
        deleteGeneric(this);
        return false;
    };
    var deleteEpisode = function () {
        console.log("start delete episode");
        deleteGeneric(this);
        return false;
    };
    var deleteMyEpisode = function () {
        console.log("start delete myepisode");
        deleteGeneric(this);
        return false;
    };
    var deleteGeneric = function (target) {
      console.log("start delete");
      console.log(target.href);
      
      $.ajax({
        type: "DELETE",
        url: target.href,
        contentType: "application/json",
        dataType: "json",
        async: false,
        data: "",
        error: ajaxErrorHandler,
        success: function(jsonObj){
          // console.log(results);
          // var jsonObj = jQuery.parseJSON(results);

           if(jsonObj === null){
                console.log("obj is null");
                showStatusMessage("Data not Saved due to Sql Error: ");

           }else{
              
              showStatusMessage("Deleted "+jsonObj.rows_affected);
              console.log(jsonObj.rows_affected);
              console.log($(target).parents("tr:first"));
              $(target).parents("tr:first").remove();
           }
           
         }
       });
    };
    var addFromExternal = function () {
      console.log("start addFromExternal episode");
      
      var $targetform = $(this).parent();
      
      $.ajax({
        type: "POST",
        dataType: "json",
        url: $targetform.attr("action"),
        data: (function() {
            return JSON.stringify({
                "program_id": $("input[name=\"program_id\"]", $targetform).val(),
                "title"     : $("input[name=\"title\"]", $targetform).val(),
                "airdate"   : $("input[name=\"airdateSql\"]", $targetform).val(),
                "season"    : $("input[name=\"season\"]", $targetform).val(),
                "season_episode_number": $("input[name=\"season_episode_number\"]", $targetform).val(),
                "overview"  : $("input[name=\"overview\"]", $targetform).val()
               });
        })(),
        accept: {
          json: "application/json"
        },
        error: ajaxErrorHandler,
        success: addEpisodeRow
      });
      return false;
    };
    var ajaxErrorHandler = function(jqXHR,error, errorThrown) {
        if(jqXHR.status&&jqXHR.status==400){
            window.alert("Error Response: " + jqXHR.responseText);
            return;
        }
        window.alert("Something went wrong\nError: " + errorThrown );
    };

    var  showStatusMessage = function(message){
        $("#js_page_message").html(message);
        
        $("#js_page_message").css("width", "100%");
        var bar_height = 51;
        var new_offset, new_position;
        if ($(document).scrollTop() > bar_height) {
            new_offset = $(document).scrollTop();
            new_position = "absolute";
        } else {
            new_offset = 0;
            new_position = "relative";
        }
        
        $("#js_page_message").css("position", new_position);
        $("#js_page_message").css("top", new_offset);

        $("#js_page_message").show().delay(3000).fadeOut();
    };

    return{
        deleteProgramHandler:deleteProgramHandler,
        addProgramHandler:addProgramHandler,
        addProgramRow:addProgramRow,
        updateProgramHandler:updateProgramHandler,
        addEpisode:addEpisode,
        deleteProgramEpisode:deleteProgramEpisode,
        deleteEpisode:deleteEpisode,
        deleteMyEpisode:deleteMyEpisode,
        addFromExternal:addFromExternal,

        //Not public, but exposed for jasmine tests
        addEpisodeRow:addEpisodeRow,
        deleteGeneric:deleteGeneric
    };
})();

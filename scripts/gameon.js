$(document).ready(function() {
   $("a").click(function() {
     alert("Hello world!");
   });
 });
$("select#typelistbox option[selected]").removeAttr("selected");
$("select#typelistbox option[value='1']").attr("selected", "selected");

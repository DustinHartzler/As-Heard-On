jQuery(document).ready(function($){$("[data-slider]").each(function(){var t=$(this);$("<span>").addClass("output").insertAfter($(this))}).bind("slider:ready slider:changed",function(t,e){$(this).nextAll(".output:first").html(e.value.toFixed(2))})});
var HHT = {};
(function ($) {
	"use strict";

	HHT.select2 = () => {
		if ($(".setupSelect2").length) {
			$(".setupSelect2").select2();
		}
	};
  
	$(document).ready(function () {
		HHT.select2();
	});
})(jQuery);

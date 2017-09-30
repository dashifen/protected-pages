(function($) {
	$(document).ready(function () {

		// this ... is ... WordPress!  so, jQuery will already been enqueued.
		// so, while we could do this without jQuery, we might as well use it
		// to try and make the following script more readable.

		var visibilityElement = $("#hidden-post-visibility");
		if (visibilityElement.length) {
			var visibility = visibilityElement.val();

			// first thing that we need to do is to remove the password
			// protected because it's a terrible option.  and we're
			// replacing it with our protected pages option.

			$("#password-span").remove();
			$("#visibility-radio-password").remove();
			$("[for=visibility-radio-password]").remove();

			// now, we want to add a the protected option in between the
			// public and private options which remain.  our insertion point
			// is the <br> before the private radio button.  that'll put our
			// new button right where the one we removed above used to be.

			var insertionPoint = $("#visibility-radio-private").prev("br");
			var protectedRadioButton = $("<input>")
				.prop("checked", visibility === "protected")
				.attr("id", "visibility-radio-protected")
				.attr("name", "visibility")
				.attr("value", "protected")
				.attr("type", "radio");

			var protectedRadioButtonLabel = $("<label>")
				.attr("for", "visibility-radio-protected")
				.text(" Protected");

			protectedRadioButton.insertBefore(insertionPoint);
			protectedRadioButtonLabel.insertBefore(insertionPoint);

			// now, the default WordPress action when the OK button is
			// clicked in the post visibility meta box won't know what to do
			// with a value of "protected."  so, we add a new action that
			// tells it what we want it to do.

			$(".save-post-visibility").click(function() {
				var visibility = $("#post-visibility-select")
					.find("input:radio:checked").val();

				if (visibility === "protected") {
					$("#post-status-display").text("Protected");
					$("#post-visibility-display").text("Protected");
					$("#hidden_post_status").val("protected");
					$(".edit-post-status").hide();
				}

			});
		}
	});
})(jQuery);

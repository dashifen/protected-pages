(function ($) {
	$(document).ready(function () {

		// we want to remove the password protected visibility and then
		// put our protected visibility in its place.

		var hiddenVisibility = $("#hidden-post-visibility");
		if (hiddenVisibility.length > 0) {
			addProtectedVisibility();
			setOnscreenVisibilityDisplay();
		}
	});

	function addProtectedVisibility() {
		var br = removePasswordProtectedVisibility();

		// now that we've removed the information about the password
		// protected visibility, we want to add in a protected visibility
		// option.  we make the input and it's label, and then we want
		// to add them into the DOM right where we took out the above
		// elements.

		var radio = getProtectedRadio();
		var label = getProtectedLabel();
		br.before(radio);
		br.before(label);

		// finally, we add a new behavior to the OK button that saves
		// the visibility setting.  we'll use an anonymous function here,
		// rather than a named one below, so that we can easily access
		// our new radio via closure.

		$(".save-post-visibility").click(function() {
			var protectedChecked = radio.prop("checked");

			if (protectedChecked) {
				$("#post-visibility-display").text("Protected");
			}

			// an input#hidden-protected-status element is added via the
			// post_submitbox_minor_actions action on the server side.

			$("#hidden-protected-status").val(protectedChecked ? "1" : "0");
		});
	}

	function removePasswordProtectedVisibility() {
		$("#visibility-radio-password").remove();
		$("label[for=visibility-radio-password]").remove();

		// the <br> before the password span in the DOM is where we want
		// to insert our new radio.  we'll grab the span, find the prior
		// br, and then we can remove the span.

		var passwordSpan = $("#password-span");
		var br = passwordSpan.prev("br");
		passwordSpan.remove();

		// finally, we return the br so that the calling scope can use it.

		return br;
	}

	function getProtectedRadio() {
		var input = $("<input>")
			.attr("id", "visibility-radio-protected")
			.attr("name", "visibility")
			.attr("type", "radio")
			.val("protected");

		var pageIsProtected = $("#hidden-protected-status").val();
		input.prop("checked", pageIsProtected);
		return input;
	}

	function getProtectedLabel() {
		return $("<label>")
			.attr("for", "visibility-radio-protected")
			.text(" Protected");
	}

	function setOnscreenVisibilityDisplay() {
		var pageIsProtected = $("#hidden-protected-status").val();
		if (pageIsProtected) {

			// if this page is protected, then we need to update the
			// on-screen display of it's visibility.  otherwise, WordPress
			// will default to "public" which isn't really accurate.

			console.log(pageIsProtected);
			$("#post-visibility-display").text("Protected");
		}
	}
})(jQuery);

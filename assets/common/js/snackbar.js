function showSnackBar(options={message: "", textColor: "white", redirectURL: null,callback: null}) {

    let snackbar = document.getElementById("snackbar");
	if (!snackbar) {
		snackbar = document.createElement('div');
		snackbar.id = 'snackbar';
		snackbar.style.visibility = 'hidden';
		snackbar.style.minWidth = '250px';
		snackbar.style.marginLeft = '-125px';
		snackbar.style.backgroundColor = '#333';
		snackbar.style.color = '#fff';
		snackbar.style.textAlign = 'center';
		snackbar.style.borderRadius = '2px';
		snackbar.style.padding = '16px';
		snackbar.style.position = 'fixed';
		snackbar.style.zIndex = '99999';
		snackbar.style.left = '50%';
		snackbar.style.bottom = '30px';
		if (document.body) {
			document.body.appendChild(snackbar);
		} else {
			// If DOM isn't ready, fallback to alert to avoid throwing.
			if (options && options.message) {
				alert(options.message);
			}
			if (options && options.redirectURL) {
				window.location.href = options.redirectURL;
			}
			if (options && typeof options.callback === 'function') {
				options.callback();
			}
			return;
		}
	}

    snackbar.innerHTML = '<span style="color: '+options.textColor+';">' + options.message + '</span>';

    snackbar.className = "show";

    setTimeout(function(){
        snackbar.className = snackbar.className.replace("show", "");
        if(options.redirectURL != null) {
            window.location.href = options.redirectURL;
        }

        if (options.callback != null ){
			if (typeof options.callback === 'function') {
				options.callback();
			}
        }

        }, 5000);
}

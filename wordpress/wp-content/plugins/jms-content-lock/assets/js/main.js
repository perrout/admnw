jQuery(document).ready(function ($) {
	// Perform AJAX login on form submit
	$('form#jmsContentLockLogin, form#jmsContentLockRegister').on('submit', function (e) {
		e.preventDefault();
		var form = $(this);
		var action = 'ajaxlogin';
		var email = form.find('#jmsContentLockEmail').val();
		var phone = '';
		var security = form.find('#jmsLoginSecurity').val();	
		form.find('#jmsContentLockStatus').show().text(ajax_login_object.loadingmessage);
		if (form.attr('id') == 'jmsContentLockRegister') {
			action = 'ajaxregister';
			email = form.find('#jmsContentLockEmail').val();
			phone = form.find('#jmsContentLockPhone').val();
			security = form.find('#jmsRegisterSecurity').val(); 
			if (phone.length < 15) {
				form.find('#jmsContentLockStatus').text('Por favor verifique o WhatsApp digitado.').show();
				form.find('#jmsContentLockPhone').val('');
				return false;
			} 	
		}  
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_login_object.ajaxurl,
			data: {
				'action' : action, //calls wp_ajax_nopriv_ajaxlogin
				'email' : email,
				'phone' : phone,
				'security' : security
			},
			success: function (data) {
				form.find('#jmsContentLockStatus').text(data.message);
				if (data.show_modal == true) {
					var registerModal = $('#jmsContentLockRegisterModal');
					registerModal.find('#jmsContentLockEmail').val(email);
					registerModal.modal('toggle');
				}
				if (data.login == true) {
					window.location.reload(true);
				}
			}
		});
		e.preventDefault();
	});

	$('#jmsContentLockPhone').on('keyup', function() {
		var phone = $(this).val();
		var result = jmsPhoneFormat(phone);
		$(this).val(result);     
	});
	$('#jmsContentLockPhone').on('blur', function() {
		var phone = $(this).val();
		var form = $('form#jmsContentLockRegister');
		if (phone.length < 15) 	{
			form.find('#jmsContentLockStatus').text('Por favor verifique o WhatsApp digitado.').show();
			$(this).val('')
		}
	});

});

function jmsPhoneFormat(phone) {
	phone = phone.replace(/[^\d]/g, '');
	if (phone.length > 0) {
	phone = "(" + phone;	
		if (phone.length > 3) {
			phone = [phone.slice(0, 3), ") ", phone.slice(3)].join('');  
		}
		if (phone.length > 10) {      
			phone = [phone.slice(0, 10), "-", phone.slice(10)].join('');
		}   
		if (phone.length > 15)                
			phone = phone.substr(0,15);
	}
	return phone;
}

// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response) {
	// console.log('statusChangeCallback');
	// console.log(response);
	// The response object is returned with a status field that lets the
	// app know the current login status of the person.
	// Full docs on the response object can be found in the documentation
	// for FB.getLoginStatus().
	if (response.status === 'connected') {
		// Logged into your app and Facebook.
		if (!ajax_login_object.loggedin) {
			registerUserAPI();
		}
	} else {
		// The person is not logged into your app or we are unable to tell.
		// document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';
	}
}

// This function is called when someone finishes with the Login
// Button.  See the onlogin handler attached to it in the sample
// code below.
function checkLoginState() {
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
		// console.log('Welcome!  Fetching your information.... ');
	});
}

window.fbAsyncInit = function() {
	FB.init({
	appId      : '762427494123275',
	cookie     : true,
	xfbml      : true,
	version    : 'v3.2'
	});
	
	FB.AppEvents.logPageView();   
	
	// FB.getLoginStatus(function(response) {
	// 	statusChangeCallback(response);
	// });

};

// Load the SDK asynchronously
(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "https://connect.facebook.net/pt_BR/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// Here we run a very simple test of the Graph API after login is
// successful.  See statusChangeCallback() for when this call is made.
function registerUserAPI() {
	FB.api('/me?fields=id,email,cover,name,first_name,last_name,age_range,link,gender,locale,picture,timezone,updated_time,verified', function(response) {
		jQuery.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajax_login_object.ajaxurl,
			data: {
				'action' : 'ajaxlogin', //calls wp_ajax_nopriv_ajaxlogin
				'email' : response.email,
				'security' : document.getElementById('jmsLoginSecurity').value
			},
			success: function (data) {
				if (data.show_modal == true) {
					jQuery(document).ready(function ($) {
						var registerModal = $('#jmsContentLockRegisterModal');
						registerModal.find('#jmsContentLockEmail').val(response.email);
						registerModal.modal('toggle');
					});
				}
				if (data.login == true) {
					window.location.reload(true);
				}
			}
		});
	});
}
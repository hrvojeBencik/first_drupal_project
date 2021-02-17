jQuery(document).ready(function ($) {
  const basicInfo = $('#basicInfo');
  const locationInfo = $('#locationInfo');
  const contactInfo = $('#contactInfo');

  const firstNextBtn = $('#firstNextBtn');
  const secondNextBtn = $('#secondNextBtn');
  const submitBtn = $('#submitBtn');

  let errorMsg = $('#errorMessage');
  errorMsg.hide();

  locationInfo.hide();
  contactInfo.hide();

  firstNextBtn.click(function() {
    basicInfo.hide();
    locationInfo.show();
  });

  secondNextBtn.click(function() {
    locationInfo.hide();
    contactInfo.show();
  });

  submitBtn.click(function() {
    const firstName = $('#firstName').val();
    const lastName = $('#lastName').val();
    const gender =  $('#gender option:selected').val();
    const country = $('#country option:selected').val();
    const city = $('#state option:selected').val();
    const email = $('#email').val();
    const confirmEmail = $('#confirmEmail').val();
    const phoneNumber = $('#phoneNumber').val();

    if(email === confirmEmail) {
      errorMsg.hide();
      const userData = {
        'first_name': firstName,
        'last_name': lastName,
        'gender': gender,
        'country': country,
        'city': city,
        'email': email,
        'confirm_email': email,
        'phone_number': phoneNumber,
      };

      const jsonUserData = JSON.stringify(userData);
      $.ajax({
        type: "POST",
        url: "/subscription",
        data: {userData: jsonUserData},
        success: function(response) {
          console.log(response);
        },
        error: function (response) {
          console.log('error');
        }
      });
    } else {
      errorMsg.text("Your email addresses do not match");
      errorMsg.css('color', 'red');
      errorMsg.show();
    }
  });
});



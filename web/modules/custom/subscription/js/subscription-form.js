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

      let ajaxUrl = '/subscription';
      $.ajax({
        type: "POST",
        url: ajaxUrl,
        data: {userData: jsonUserData},
        success: function(response) {
          //This can only be "true" or "false", so it is enough to check only first letter
          const info = response.substring(11,12);
          const isValid = info === "t";

          if(isValid) {
            errorMsg.text("Subscription succeeded");
            errorMsg.css('color', 'green');
            errorMsg.show();
            window.location.href = '/';
          } else {
            errorMsg.text("You are already subscribed");
            errorMsg.css('color', 'red');
            errorMsg.show();
          }
        },
        error: function (response) {
          console.log("Error");
          console.log(response);
        }
      });
    } else {
      errorMsg.text("Your email addresses do not match");
      errorMsg.css('color', 'red');
      errorMsg.show();
    }
  });
});

let input = document.querySelector("#whatsapp");
let iti = window.intlTelInput(input, {
  initialCountry: "auto",
  geoIpLookup: function(callback) {
    $.get("https://ipinfo.io", function() {}, "jsonp").always(function(resp) {
      const countryCode = (resp && resp.country) ? resp.country : "us";
      callback(countryCode);
    });
  },
  hiddenInput: "full_phone",
  formatOnDisplay:false,
  separateDialCode:true,
  utilsScript:"https://s3-us-west-2.amazonaws.com/s.cdpn.io/32471/utils.js",
});
iti.promise.then(function() {
  var fullNumber = iti.getSelectedCountryData().dialCode;
  document.getElementById("code").value = fullNumber;
});

input.addEventListener("input", function() {
  var fullNumber = iti.getSelectedCountryData().dialCode;
  document.getElementById("code").value = fullNumber;
});